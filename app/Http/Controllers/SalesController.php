<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['items', 'cashier'])->latest();

        // Date range filter
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Payment method filter
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by invoice number or customer
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $sales = $query->paginate(20);

        // Sales statistics
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $monthSales = Sale::whereMonth('created_at', now()->month)->sum('total_amount');
        $totalSales = Sale::count();

        $currency_symbol = get_currency_symbol();

        return view('sales.index', compact('sales', 'todaySales', 'monthSales', 'totalSales', 'currency_symbol'));
    }

    public function show(Sale $sale)
    {
        $currency_symbol = get_currency_symbol();
        $sale->load(['items.product', 'items.batch', 'cashier']);
        return view('sales.show', compact('sale', 'currency_symbol'));
    }

    /*public function completeSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,card,digital',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $items = [];

            // Calculate totals and prepare items
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $batch = $this->getAvailableBatch($product, $itemData['quantity']);

                if (!$batch) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                $unitPrice = $product->price;
                $quantity = $itemData['quantity'];
                $totalPrice = $unitPrice * $quantity;

                $subtotal += $totalPrice;

                $items[] = [
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ];

                // Update batch quantity
                $batch->decrement('quantity', $quantity);
            }

            // Calculate tax and total
            $taxAmount = $subtotal * 0.08; // 8% tax
            $discountType = $request->discountType;
            $discountAmount = $request->discount ?? 0;
            $discount_value = $discountType === "percentage" ? ($subtotal * ($discountAmount / 100)) : $discountAmount;
            $totalAmount = $subtotal + $taxAmount - $discount_value;

            // Create sale
            $sale = Sale::create([
                'cashier_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount_value,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create sale items
            $sale->items()->createMany($items);

            // Update product stocks
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->update([
                        'stock' => $product->batches()->sum('quantity')
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'message' => 'Sale completed successfully!'
            ]);
        });
    }*/

    public function completeSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            // optional client allocations: items.*.allocations => array of { batch_id, qty }
            'items.*.allocations' => 'nullable|array',
            'items.*.allocations.*.batch_id' => 'required_with:items.*.allocations|integer|exists:batches,id',
            'items.*.allocations.*.qty' => 'required_with:items.*.allocations|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,card,digital',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'discountType' => 'nullable|in:percentage,fixed'
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $saleItemsForCreate = []; // will contain rows to create in sale_items

            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $requiredQty = (int)$itemData['quantity'];

                // If client provided allocations, attempt to validate & apply them.
                // Otherwise compute allocations server-side (FEFO).
                $allocations = $itemData['allocations'] ?? null;

                if ($allocations && is_array($allocations) && count($allocations) > 0) {
                    // Validate allocations sum equals requiredQty (or allow partial based on your business rule)
                    $sumAlloc = array_sum(array_map(fn($a) => (int)$a['qty'], $allocations));
                    if ($sumAlloc !== $requiredQty) {
                        throw new \Exception("Allocations quantity mismatch for product {$product->name} (expected {$requiredQty}, got {$sumAlloc})");
                    }

                    // Apply each allocation atomically (decrement batch quantities)
                    foreach ($allocations as $alloc) {
                        $batchId = (int)$alloc['batch_id'];
                        $qty = (int)$alloc['qty'];
                        if ($qty <= 0) continue;

                        // Atomic decrement: will only decrement if current quantity >= $qty
                        $updated = ProductBatch::where('id', $batchId)
                            ->where('quantity', '>=', $qty)
                            ->decrement('quantity', $qty);

                        if (!$updated) {
                            // allocation conflict or insufficient quantity
                            throw new \Exception("Allocation failed for batch #{$batchId} ({$product->name}) — insufficient quantity or concurrent sale.");
                        }

                        $unitPrice = $product->price;
                        $totalPrice = $unitPrice * $qty;
                        $subtotal += $totalPrice;

                        $saleItemsForCreate[] = [
                            'product_id' => $product->id,
                            'product_batch_id' => $batchId,
                            'product_name' => $product->name,
                            'unit_price' => $unitPrice,
                            'quantity' => $qty,
                            'total_price' => $totalPrice,
                        ];
                    }
                } else {
                    // No client allocations provided: compute server-side allocations (FEFO then FIFO)
                    $allocs = $this->allocateBatchesForSale($product->id, $requiredQty);

                    // If not enough allocated
                    $sumAlloc = array_sum(array_map(fn($a) => $a['qty'], $allocs));
                    if ($sumAlloc < $requiredQty) {
                        throw new \Exception("Insufficient stock for {$product->name} (requested: {$requiredQty}, available: {$sumAlloc})");
                    }

                    // Apply allocations atomically (decrement)
                    foreach ($allocs as $a) {
                        $batchId = $a['batch_id'];
                        $qty = $a['qty'];

                        $updated = ProductBatch::where('id', $batchId)
                            ->where('quantity', '>=', $qty)
                            ->decrement('quantity', $qty);

                        if (!$updated) {
                            // concurrent conflict — fail early so client can retry
                            throw new \Exception("Allocation conflict while reserving batch #{$batchId} for {$product->name}");
                        }

                        $unitPrice = $product->price;
                        $totalPrice = $unitPrice * $qty;
                        $subtotal += $totalPrice;

                        $saleItemsForCreate[] = [
                            'product_id' => $product->id,
                            'product_batch_id' => $batchId,
                            'product_name' => $product->name,
                            'unit_price' => $unitPrice,
                            'quantity' => $qty,
                            'total_price' => $totalPrice,
                        ];
                    }
                }
            } // end foreach items

            // Calculate tax and total
            $taxAmount = $subtotal * 0.08; // 8% tax
            $discountType = $request->discountType ?? null;
            $discountAmount = $request->discount ?? 0;
            $discount_value = $discountType === "percentage" ? ($subtotal * ($discountAmount / 100)) : $discountAmount;
            $totalAmount = $subtotal + $taxAmount - $discount_value;

            // Create sale
            $sale = Sale::create([
                'cashier_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount_value,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create sale items (we created one row per allocation above)
            $sale->items()->createMany($saleItemsForCreate);

            // Update product stocks (sum of remaining batch quantities)
            $productIds = collect($saleItemsForCreate)->pluck('product_id')->unique();
            foreach ($productIds as $pid) {
                $product = Product::find($pid);
                if ($product) {
                    $product->stock = $product->batches()->sum('quantity');
                    $product->save();
                }
            }

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number ?? null,
                'message' => 'Sale completed successfully!'
            ]);
        });
    }

    /**
     * Allocate batches for a product (FEFO then FIFO fallback).
     * Returns array of ['batch_id' => id, 'qty' => x]
     */
    private function allocateBatchesForSale(int $productId, int $requiredQty): array
    {
        $now = Carbon::now()->toDateString();

        $batches = ProductBatch::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($now) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $now);
            })
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $allocations = [];
        $remaining = $requiredQty;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;
            $take = min($batch->quantity, $remaining);
            if ($take <= 0) continue;
            $allocations[] = ['batch_id' => $batch->id, 'qty' => (int)$take];
            $remaining -= $take;
        }

        return $allocations;
    }

    private function getAvailableBatch(Product $product, int $quantity)
    {
        // Get the oldest batch with sufficient quantity (FIFO)
        return $product->batches()
            ->where('quantity', '>=', $quantity)
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date')
            ->orderBy('created_at')
            ->first();
    }

    public function printInvoice(Sale $sale)
    {
        $sale->load(['items.product', 'items.batch', 'cashier']);
        $settings = \App\Models\SystemSetting::getSettings();
        $imagePath = public_path('images\clinic-logo.png');
        $currency_symbol = get_currency_symbol();
        return view('sales.print', compact('sale', 'settings', 'imagePath', 'currency_symbol'));
    }

    public function destroy(Sale $sale)
    {
        // In a real application, you might want to implement sale return/refund logic
        // instead of direct deletion

        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Sale record deleted successfully!');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'payment_method', 'search']);
        $filename = 'sales-report-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(new SalesExport($filters), $filename);
    }

    public function exportPDF(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'payment_method', 'search']);

        // Get sales data with the same filters
        $query = Sale::with(['items', 'cashier']);

        if (isset($filters['start_date']) && $filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['payment_method']) && $filters['payment_method']) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $sales = $query->latest()->get();

        // Calculate totals for the report
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total_amount');
        $totalTax = $sales->sum('tax_amount');
        $totalDiscount = $sales->sum('discount_amount');

        $pdf = Pdf::loadView('sales.exports.pdf', compact('sales', 'filters', 'totalSales', 'totalRevenue', 'totalTax', 'totalDiscount'));

        $filename = 'sales-report-' . now()->format('Y-m-d-H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
