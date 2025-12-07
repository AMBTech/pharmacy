<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['batches', 'activeBatches', 'category'])->withCount('batches');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'low':
                    $query->where('stock', '<', 10);
                    break;
                case 'out':
                    $query->where('stock', '=', 0);
                    break;
                case 'sufficient':
                    $query->where('stock', '>=', 10);
                    break;
            }
        }

        $products = $query->latest()->paginate(20);

        $categories = Category::active()->ordered()->get();

        $currency_symbol = get_currency_symbol();


        return view('inventory.index', compact('products', 'categories', 'currency_symbol'));
    }

    public function create()
    {
        $currency_symbol = get_currency_symbol();
        return view('inventory.create', compact('currency_symbol'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category_id' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'barcode' => 'required|numeric|min:8',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['stock'] = 0; // Initial stock will be added via batches

        $product = Product::create($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Product created successfully!');
    }

    public function batchManagement(Product $product)
    {
        $currency_symbol = get_currency_symbol();
        $product->load('batches');
        return view('inventory.partials.batch-management', compact('product', 'currency_symbol'));
    }

    public function edit(Product $product)
    {
        $currency_symbol = get_currency_symbol();
        $product->load(['batches', 'activeBatches']);

        return view('inventory.edit', compact('product', 'currency_symbol'));
    }


    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category_id' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'barcode' => 'required|digits:8',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('inventory.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Check if product has any sales before deleting
        // You might want to add this check later when you have sales relationships

        $product->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function addBatch(Request $request, Product $product)
    {
        $validated = $request->validate([
            'batch_number' => 'required|string|max:255',
            'manufacturing_date' => 'required|date',
            'expiry_date' => 'required|date|after:manufacturing_date',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product->batches()->create($validated);

        // Update product stock
        $product->update([
            'stock' => $product->batches()->sum('quantity')
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Batch added successfully!'
            ]);
        }

        return back()->with('success', 'Batch added successfully!');
    }

    public function deleteBatch(ProductBatch $batch)
    {
        $product = $batch->product;
        $batch->delete();

        // Update product stock
        $product->update([
            'stock' => $product->batches()->sum('quantity')
        ]);

        return back()->with('success', 'Batch deleted successfully!');
    }

    public function getBatchesJson($product)
    {
        return ProductBatch::query()->where('product_id', $product)->get();
    }
}
