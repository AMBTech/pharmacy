<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductBatch;
use Barryvdh\DomPDF\Facade\Pdf;
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

        return view('sales.index', compact('sales', 'todaySales', 'monthSales', 'totalSales'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'items.batch', 'cashier']);
        return view('sales.show', compact('sale'));
    }

    public function completeSale(Request $request)
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
        return view('sales.print', compact('sale'));
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
