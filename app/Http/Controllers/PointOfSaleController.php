<?php

namespace App\Http\Controllers;

use App\Models\HeldSale;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointOfSaleController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)
            ->with('batches')
            ->get()
            ->map(function ($product) {
                $product->current_stock = $product->batches->sum('quantity');
                return $product;
            });

        $settings = SystemSetting::getSettings();
        $settings['currency'] = format_currency($settings['currency']);

        return view('pos.index', compact('products', 'settings'));
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q');

        $products = Product::where('is_active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            })
            ->with('batches')
            ->get()
            ->map(function ($product) {
                $product->current_stock = $product->batches->sum('quantity');
                return $product;
            });

        return response()->json($products);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check stock availability
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock!'
            ], 400);
        }

        // In a real app, you'd manage the cart server-side or use sessions
        return response()->json([
            'success' => true,
            'message' => 'Product added to cart'
        ]);
    }

    /*public function completeSale(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'subtotal' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'tax' => 'required|numeric|min:0',
                'discount' => 'required|numeric|min:0',
                'paymentMethod' => 'required|string|in:cash,card,digital',
                'customerName' => 'nullable|string|max:255',
                'customerPhone' => 'nullable|string|max:20'
            ]);

            // Generate invoice number
            $today = now()->format('Ymd');
            $lastInvoice = Sale::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = "INV-$today-" . str_pad($lastInvoice, 4, '0', STR_PAD_LEFT);

            // Create Sale record
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'cashier_id' => '1', //auth()->id(),
                'customer_name' => $request->customerName,
                'customer_phone' => $request->customerPhone,
                'subtotal' => $request->subtotal,
                'tax_amount' => $request->tax,
                'discount_amount' => $request->discount,
                'total_amount' => $request->total,
                'payment_method' => $request->paymentMethod,
            ]);

            // Process sale items and update stock
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['id']);

                // Check stock availability
                if ($product->stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$itemData['quantity']}");
                }

                // Get the oldest batch with sufficient quantity (FIFO)
                $batch = $product->batches()
                    ->where('quantity', '>=', $itemData['quantity'])
                    ->where('expiry_date', '>', now())
                    ->orderBy('expiry_date')
                    ->orderBy('created_at')
                    ->first();

                if (!$batch) {
                    throw new \Exception("No valid batch found for {$product->name}");
                }

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $batch->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $itemData['quantity'],
                    'total_price' => $product->price * $itemData['quantity'],
                ]);

                // Update batch quantity
                $batch->decrement('quantity', $itemData['quantity']);

                // Update product total stock
                $product->update([
                    'stock' => $product->batches()->sum('quantity')
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }*/

    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $product = Product::where('is_active', true)
            ->where(function ($query) use ($request) {
                $query->where('barcode', $request->barcode)
                    ->orWhere('id', $request->barcode);
            })
            ->with('batches')
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->current_stock = $product->batches->sum('quantity');

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    public function holdSale(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0',
        ]);

        try {
            $heldSale = HeldSale::create([
                'cashier_id' => auth()->id(),
                'customer_name' => $request->customerName,
                'customer_phone' => $request->customerPhone,
                'cart_data' => $request->all(),
                'subtotal' => $request->subtotal,
                'tax_amount' => $request->tax,
                'discount_amount' => $request->discount,
                'total_amount' => $request->total,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'hold_id' => $heldSale->hold_id,
                'message' => 'Sale held successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hold sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function releaseSale($holdId)
    {
        try {
            $heldSale = HeldSale::where('hold_id', $holdId)->firstOrFail();

            if ($heldSale->is_expired) {
                return response()->json([
                    'success' => false,
                    'message' => 'This held sale has expired and cannot be released.'
                ], 400);
            }

            $cartData = $heldSale->cart_data;

            // Delete the held sale after releasing
            $heldSale->delete();

            return response()->json([
                'success' => true,
                'cart_data' => $cartData,
                'message' => 'Sale released successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to release sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteHeldSale($holdId)
    {
        try {
            $heldSale = HeldSale::where('hold_id', $holdId)->firstOrFail();
            $heldSale->delete();

            return response()->json([
                'success' => true,
                'message' => 'Held sale deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete held sale: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getHeldSales()
    {
        $heldSales = HeldSale::with('cashier')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($heldSale) {
                return [
                    'hold_id' => $heldSale->hold_id,
                    'customer_name' => $heldSale->customer_name,
                    'customer_phone' => $heldSale->customer_phone,
                    'cart_data' => $heldSale->cart_data,
                    'subtotal' => $heldSale->subtotal,
                    'tax_amount' => $heldSale->tax_amount,
                    'discount_amount' => $heldSale->discount_amount,
                    'total_amount' => $heldSale->total_amount,
                    'held_at' => $heldSale->held_at->toISOString(),
                    'expires_at' => $heldSale->expires_at->toISOString(),
                    'is_expired' => $heldSale->is_expired,
                    'cashier_name' => $heldSale->cashier->name ?? 'System'
                ];
            });

        return response()->json([
            'success' => true,
            'held_sales' => $heldSales
        ]);
    }

}
