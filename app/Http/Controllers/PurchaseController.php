<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'user'])
            ->latest()
            ->paginate(20);

        $currency = get_currency_symbol();

        return view('purchases.index', compact('purchaseOrders', 'currency'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::with('category')
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                $product->stock = $product->activeBatchesStock;
                return $product;
            });

        $currency = get_currency_symbol();

        return view('purchases.create', compact('suppliers', 'products', 'currency'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'tax' => 0, // Will be calculated based on tax rate
                'status' => 'draft'
            ]);

            foreach ($validated['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'manufacturing_date' => $item['manufacturing_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => $item['notes'] ?? null
                ]);
            }

            $purchaseOrder->tax = $purchaseOrder->subtotal * (($validated['tax_rate'] ?? 0) / 100);
            $purchaseOrder->save();

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase order created successfully.',
                    'redirect' => route('purchases.show', $purchaseOrder)
                ]);
            }

            return redirect()->route('purchases.show', $purchaseOrder)
                ->with('success', 'Purchase order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);

        $currency = get_currency_symbol();

        return view('purchases.show', compact('purchase', 'currency'));
    }

    public function edit(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'draft') {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Only draft purchase orders can be edited.');
        }

        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::with('category')->where('is_active', true)->get();

        $purchase->load(['items.product']);

        $currency = get_currency_symbol();

        return view('purchases.create', compact('purchase', 'suppliers', 'products', 'currency'));
    }

    public function update(Request $request, PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be edited.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.notes' => 'nullable|string'
        ]);


        DB::beginTransaction();

        try {
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
            ]);

            // Update or create items
            $existingItemIds = $purchase->items->pluck('id')->toArray();
            $updatedItemIds = [];

            foreach ($validated['items'] as $item) {
                if (isset($item['id']) && in_array($item['id'], $existingItemIds)) {
                    // Update existing item
                    $purchaseItem = $purchase->items()->find($item['id']);
                    $purchaseItem->update([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'batch_number' => $item['batch_number'] ?? null,
                        'manufacturing_date' => $item['manufacturing_date'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'notes' => $item['notes'] ?? null
                    ]);
                    $updatedItemIds[] = $item['id'];
                } else {
                    // Create new item
                    $newItem = $purchase->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'batch_number' => $item['batch_number'] ?? null,
                        'manufacturing_date' => $item['manufacturing_date'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                        'notes' => $item['notes'] ?? null
                    ]);
                    $updatedItemIds[] = $newItem->id;
                }
            }

            // Remove deleted items
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                $purchase->items()->whereIn('id', $itemsToDelete)->delete();
            }

            $purchase->tax = $purchase->subtotal * (($validated['tax_rate'] ?? 0) / 100);
            $purchase->save();

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase order updated successfully.',
                    'redirect' => route('purchases.show', $purchase)
                ]);
            }

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update purchase order: ' . $e->getMessage());
        }
    }

    public function destroy(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be deleted.');
        }

        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    public function markAsOrdered(PurchaseOrder $purchase)
    {
        if ($purchase->status !== 'draft') {
            return back()->with('error', 'Only draft purchase orders can be marked as ordered.');
        }

        $purchase->update(['status' => 'ordered']);

        return back()->with('success', 'Purchase order marked as ordered.');
    }

    public function receive(PurchaseOrder $purchase)
    {
        if (!in_array($purchase->status, ['ordered', 'partial'])) {
            return redirect()->route('purchases.show', $purchase)
                ->with('error', 'Only ordered or partially received purchase orders can be received.');
        }

        $purchase->load(['items.product', 'supplier']);

        $currency = get_currency_symbol();

        return view('purchases.receive', compact('purchase', 'currency'));
    }

    public function receiveStore(Request $request, PurchaseOrder $purchase)
    {
        if (!in_array($purchase->status, ['ordered', 'partial'])) {
            return back()->with('error', 'Only ordered or partially received purchase orders can be received.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.received_quantity' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date|after:manufacturing_date',
            'receiving_notes' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $isFirstReceival = $purchase->status === 'ordered';
            $totalReceivedValue = 0;

            foreach ($validated['items'] as $itemId => $itemData) {
                $item = $purchase->items()->find($itemId);

                if (!$item) {
                    continue;
                }

                $receivedQty = $itemData['received_quantity'];

                if ($receivedQty > 0) {
                    // Update received quantity
                    $newReceivedQty = $item->received_quantity + $receivedQty;
                    if ($newReceivedQty > $item->quantity) {
                        throw new \Exception("Received quantity cannot exceed ordered quantity for product: " . $item->product->name);
                    }

                    $item->update([
                        'received_quantity' => $newReceivedQty,
                        'batch_number' => $itemData['batch_number'] ?? $item->batch_number,
                        'manufacturing_date' => $itemData['manufacturing_date'] ?? $item->manufacturing_date,
                        'expiry_date' => $itemData['expiry_date'] ?? $item->expiry_date
                    ]);

                    // Add to inventory batch
                    if ($item->product) {
                        $batch = $item->product->batches()->create([
                            'batch_number' => $itemData['batch_number'] ?? $item->batch_number ?? 'BATCH-' . time() . '-' . $item->product->id,
                            'manufacturing_date' => $itemData['manufacturing_date'] ?? $item->manufacturing_date,
                            'expiry_date' => $itemData['expiry_date'] ?? $item->expiry_date,
                            'quantity' => (int) $receivedQty,
                            'cost_price' => $item->unit_cost,
                            'selling_price' => $item->product->price
                        ]);

                        // Update product stock
                        $item->product->increment('stock', (int) $receivedQty);
                    }

                    $totalReceivedValue += $receivedQty * $item->unit_cost;
                }
            }

            // Update supplier's total purchases only on first complete receival
            if ($isFirstReceival) {
                $allItemsReceived = $purchase->items->every(function ($item) {
                    return $item->received_quantity >= $item->quantity;
                });

                if ($allItemsReceived) {
                    $supplier = $purchase->supplier;
                    $supplier->increment('total_purchases', $purchase->total);
                }
            }

            // Update purchase order
            if (!$purchase->delivery_date) {
                $purchase->delivery_date = now();
            }

            if ($validated['receiving_notes']) {
                $purchase->notes = ($purchase->notes ? $purchase->notes . "\n\n" : '') .
                                  'Receiving Notes (' . now()->format('Y-m-d H:i') . '): ' .
                                  $validated['receiving_notes'];
            }

            $purchase->save();
            $purchase->updateReceivedStatus();

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Products received successfully and added to inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to receive products: ' . $e->getMessage());
        }
    }

    // Supplier Management Methods
    public function suppliers()
    {
        $currency = get_currency_symbol();

        $suppliers = Supplier::latest()->paginate(20);
        return view('purchases.suppliers', compact('suppliers', 'currency'));
    }

    public function createSupplier()
    {
        return view('purchases.supplier-create');
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Supplier::create($validated);

        return redirect()->route('purchases.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function editSupplier(Supplier $supplier)
    {
        return view('purchases.supplier-create', compact('supplier'));
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $supplier->update($validated);

        return redirect()->route('purchases.suppliers')
            ->with('success', 'Supplier updated successfully.');
    }

    public function showSupplier(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders' => function ($query) {
            $query->latest()->limit(10);
        }]);

        $currency = get_currency_symbol();

        return view('purchases.supplier-show', compact('supplier', 'currency'));
    }
}
