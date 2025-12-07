<?php

namespace App\Http\Controllers;

use App\Exports\InventoryExport;
use App\Models\Category;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        // Quick stats for dashboard
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $monthSales = Sale::whereMonth('created_at', now()->month)->sum('total_amount');
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<', 10)->where('stock', '>', 0)->count();
        $expiringSoon = ProductBatch::where('expiry_date', '<=', now()->addDays(30))->count();

        $currency_symbol = get_currency_symbol();

        return view('reports.index', compact(
            'todaySales',
            'monthSales',
            'totalProducts',
            'lowStockProducts',
            'expiringSoon',
            'currency_symbol'
        ));
    }

    public function salesTrends(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly
        $days = $request->get('days', 30);

        $salesData = $this->getSalesTrendsData($period, $days);
        $topProducts = $this->getTopProducts(10);
        $paymentMethods = $this->getPaymentMethodDistribution();
        $currency_symbol = get_currency_symbol();

        return view('reports.sales-trends', compact('salesData', 'topProducts', 'paymentMethods', 'period', 'days', 'currency_symbol'));
    }

    public function inventory(Request $request)
    {
        // Get all filters from request
        $filters = $request->only([
            'name', 'barcode', 'category', 'brand', 'supplier',
            'stock_status', 'stock_min', 'stock_max',
            'batch_number', 'show_batches', 'expiry_start', 'expiry_end',
            'price_min', 'price_max', 'cost_min', 'cost_max', 'margin_min', 'margin_max',
            'movement', 'last_sold_start', 'last_sold_end',
            'sort_by', 'sort_dir', 'per_page', 'as_of_date'
        ]);

        // Set defaults
        $filters['stock_status'] = $filters['stock_status'] ?? 'all';
        $filters['sort_by'] = $filters['sort_by'] ?? 'name';
        $filters['sort_dir'] = $filters['sort_dir'] ?? 'asc';
        $filters['per_page'] = $filters['per_page'] ?? 50;
        $filters['category'] = $filters['category'] ?? 'all';
        $filters['brand'] = $filters['brand'] ?? 'all';
        $filters['supplier'] = $filters['supplier'] ?? 'all';

        // Build query
        $query = Product::with(['category', 'batches']);

        // Apply filters
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['barcode'])) {
            $query->where('barcode', 'like', '%' . $filters['barcode'] . '%');
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['brand']) && $filters['brand'] !== 'all') {
            $query->where('brand', $filters['brand']);
        }

        // Stock status filter
        if (!empty($filters['stock_status']) && $filters['stock_status'] !== 'all') {
            $settings = \App\Models\SystemSetting::getSettings();
            switch ($filters['stock_status']) {
                case 'in-stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'out-of-stock':
                    $query->where('stock', '<=', 0);
                    break;
                case 'low-stock':
                    $query->where('stock', '>', 0)
                          ->where('stock', '<=', $settings->low_stock_threshold);
                    break;
                case 'zero-stock':
                    $query->where('stock', 0);
                    break;
            }
        }

        // Stock range
        if (!empty($filters['stock_min'])) {
            $query->where('stock', '>=', $filters['stock_min']);
        }
        if (!empty($filters['stock_max'])) {
            $query->where('stock', '<=', $filters['stock_max']);
        }

        // Price range
        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        // Batch filters
        if (!empty($filters['batch_number'])) {
            $query->whereHas('batches', function($q) use ($filters) {
                $q->where('batch_number', 'like', '%' . $filters['batch_number'] . '%');
            });
        }

        if (!empty($filters['expiry_start'])) {
            $query->whereHas('batches', function($q) use ($filters) {
                $q->where('expiry_date', '>=', $filters['expiry_start']);
            });
        }

        if (!empty($filters['expiry_end'])) {
            $query->whereHas('batches', function($q) use ($filters) {
                $q->where('expiry_date', '<=', $filters['expiry_end']);
            });
        }

        // Apply movement filter (needs to be before pagination)
        if (!empty($filters['movement']) && is_array($filters['movement'])) {
            $allProducts = $query->get();
            $movementDataAll = $this->getMovementData($allProducts->pluck('id')->toArray());

            $filteredProductIds = [];
            foreach ($allProducts as $product) {
                $movement = $movementDataAll[$product->id]['movement'] ?? 'unknown';
                if (in_array($movement, $filters['movement'])) {
                    $filteredProductIds[] = $product->id;
                }
            }

            $query->whereIn('id', $filteredProductIds);
        }

        // Sorting
        $query->orderBy($filters['sort_by'], $filters['sort_dir']);

        // Get paginated results
        $products = $query->paginate($filters['per_page']);

        // Get movement data for display
        $movementData = $this->getMovementData($products->pluck('id')->toArray());

        // Get additional data
        $categories = \App\Models\Category::all();
        $brands = Product::whereNotNull('brand')->distinct()->pluck('brand');
        $suppliers = \App\Models\Supplier::all();

        // Calculate totals
        $totalItems = Product::sum('stock');
        $totalStockValue = Product::sum(DB::raw('stock * price'));

        return view('reports.inventory', compact(
            'products',
            'filters',
            'categories',
            'brands',
            'suppliers',
            'movementData',
            'totalItems',
            'totalStockValue'
        ));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $profitLossData = $this->getProfitLossData($startDate, $endDate);
        $revenueExpenses = $this->getRevenueExpensesTrend($startDate, $endDate);
        $profitByCategory = $this->getProfitByCategory($startDate, $endDate);
        $currency_symbol = get_currency_symbol();

        return view('reports.profit-loss', compact('profitLossData', 'revenueExpenses', 'profitByCategory', 'startDate', 'endDate', 'currency_symbol'));
    }

    public function expiringProducts(Request $request)
    {
        // Default filters
        $filters = [
            'expiry_within_days' => (int) $request->get('expiry_within_days', 30),
            'expiry_start' => $request->get('expiry_start'),
            'expiry_end' => $request->get('expiry_end'),
            'expired_only' => (bool) $request->get('expired_only', false),

            'batch_number' => $request->get('batch_number'),
            'manufacture_start' => $request->get('manufacture_start'),
            'manufacture_end' => $request->get('manufacture_end'),
            'batch_status' => $request->get('batch_status', 'active'), // active, inactive, all

            'product_name' => $request->get('product_name'),
            'barcode' => $request->get('barcode'),
            'category_id' => $request->get('category_id'),
            'brand' => $request->get('brand'),
            'generic_name' => $request->get('generic_name'),

            'supplier_id' => $request->get('supplier_id'),

            'stock_status' => $request->get('stock_status'), // in-stock, zero-stock, low-stock

            'sort_by' => $request->get('sort_by', 'expiry_date'), // expiry_date, product_name, batch_number
            'sort_dir' => $request->get('sort_dir', 'asc'),

            'per_page' => $request->get('per_page', 50)
        ];

        // Build query
        $query = ProductBatch::with(['product.category', 'product.batches'])
            ->join('products', 'product_batches.product_id', '=', 'products.id');

        // 1. Expiry Date Filters
        if ($filters['expired_only']) {
            $query->where('product_batches.expiry_date', '<', now());
        } else {
            if (!empty($filters['expiry_start'])) {
                $query->where('product_batches.expiry_date', '>=', $filters['expiry_start']);
            } else {
                // Default: show items not yet expired
                $query->where('product_batches.expiry_date', '>=', now());
            }

            if (!empty($filters['expiry_end'])) {
                $query->where('product_batches.expiry_date', '<=', $filters['expiry_end']);
            } elseif (!empty($filters['expiry_within_days'])) {
                // Expiring within X days
                $query->where('product_batches.expiry_date', '<=', now()->addDays($filters['expiry_within_days']));
            }
        }

        // 2. Batch Filters
        if (!empty($filters['batch_number'])) {
            $query->where('product_batches.batch_number', 'like', '%' . $filters['batch_number'] . '%');
        }

        if (!empty($filters['manufacture_start'])) {
            $query->where('product_batches.manufacturing_date', '>=', $filters['manufacture_start']);
        }

        if (!empty($filters['manufacture_end'])) {
            $query->where('product_batches.manufacturing_date', '<=', $filters['manufacture_end']);
        }

        if ($filters['batch_status'] === 'active') {
            $query->where('product_batches.quantity', '>', 0);
        } elseif ($filters['batch_status'] === 'inactive') {
            $query->where('product_batches.quantity', '=', 0);
        }

        // 3. Product Filters
        if (!empty($filters['product_name'])) {
            $query->where('products.name', 'like', '%' . $filters['product_name'] . '%');
        }

        if (!empty($filters['barcode'])) {
            $query->where('products.barcode', 'like', '%' . $filters['barcode'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['brand'])) {
            $query->where('products.brand', 'like', '%' . $filters['brand'] . '%');
        }

        if (!empty($filters['generic_name'])) {
            $query->where('products.generic_name', 'like', '%' . $filters['generic_name'] . '%');
        }

        // 4. Supplier Filter (via purchase orders)
        if (!empty($filters['supplier_id'])) {
            $query->whereExists(function($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('purchase_order_items')
                    ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                    ->whereColumn('purchase_order_items.product_id', 'products.id')
                    ->where('purchase_orders.supplier_id', $filters['supplier_id']);
            });
        }

        // 5. Stock Status Filters
        if ($filters['stock_status'] === 'in-stock') {
            $query->where('products.stock', '>', 0);
        } elseif ($filters['stock_status'] === 'zero-stock') {
            $query->where('products.stock', '=', 0);
        } elseif ($filters['stock_status'] === 'low-stock') {
            $query->where('products.stock', '>', 0)
                  ->where('products.stock', '<', 10);
        }

        // 6. Sorting
        $sortColumn = match($filters['sort_by']) {
            'product_name' => 'products.name',
            'batch_number' => 'product_batches.batch_number',
            default => 'product_batches.expiry_date'
        };

        $query->orderBy($sortColumn, $filters['sort_dir']);

        // Select columns
        $query->select('product_batches.*');

        // Get paginated results
        $expiringProducts = $query->paginate($filters['per_page'])->appends($filters);

        // Add days until expiry to each batch
        $expiringProducts->getCollection()->transform(function ($batch) {
            $batch->days_until_expiry = now()->diffInDays($batch->expiry_date, false);
            return $batch;
        });

        // Get summary statistics
        $totalBatchesExpiring = ProductBatch::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->where('quantity', '>', 0)
            ->count();

        $totalExpiredBatches = ProductBatch::where('expiry_date', '<', now())
            ->where('quantity', '>', 0)
            ->count();

        $totalValueAtRisk = ProductBatch::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->where('quantity', '>', 0)
            ->sum(DB::raw('quantity * cost_price'));

        // Get additional data for filters
        $categories = \App\Models\Category::all();
        $brands = Product::whereNotNull('brand')->distinct()->pluck('brand');
        $suppliers = \App\Models\Supplier::where('is_active', true)->get();

        $expiryTrend = $this->getExpiryTrend();

        return view('reports.expiring-products', compact(
            'expiringProducts',
            'filters',
            'categories',
            'brands',
            'suppliers',
            'totalBatchesExpiring',
            'totalExpiredBatches',
            'totalValueAtRisk',
            'expiryTrend'
        ));
    }

    public function salesByCashier(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $cashierId = $request->get('cashier_id');

        $cashierPerformance = $this->getCashierPerformance($startDate, $endDate, $cashierId);
        $cashierTrends = $this->getCashierTrends($startDate, $endDate, $cashierId);

        // Get all cashiers for the dropdown
        $allCashiers = User::whereHas('sales')
            ->with('role')
            ->orderBy('name')
            ->get();

        return view('reports.sales-by-cashier', compact('cashierPerformance', 'cashierTrends', 'startDate', 'endDate', 'cashierId', 'allCashiers'));
    }

    public function salesByCategory(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $categorySales = $this->getCategorySales($startDate, $endDate);
        $categoryTrend = $this->getCategoryTrend($startDate, $endDate);

        return view('reports.sales-by-category', compact('categorySales', 'categoryTrend', 'startDate', 'endDate'));
    }

    public function dailySales(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));

        $dailySales = $this->getDailySales($date);
        $hourlySales = $this->getHourlySales($date);
        $topProductsDaily = $this->getTopProductsDaily($date);
        $currency_symbol = get_currency_symbol();

        return view('reports.daily-sales', compact('dailySales', 'hourlySales', 'topProductsDaily', 'date', 'currency_symbol'));
    }

    // Private methods for data aggregation

    private function getSalesTrendsData($period, $days)
    {
        $endDate = now();
        $startDate = now()->subDays($days);

        return Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as transactions'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('AVG(total_amount) as average_sale')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getTopProducts($limit = 10)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.name',
                'categories.name as category',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->groupBy('sale_items.product_id', 'products.name', 'categories.name')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getPaymentMethodDistribution()
    {
        return Sale::select(
            'payment_method',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total_amount) as amount')
        )
            ->groupBy('payment_method')
            ->get();
    }

    private function getInventoryData($stockStatus)
    {
        $query = Product::with(['batches', 'activeBatches']);

        switch ($stockStatus) {
            case 'low':
                $query->where('stock', '<', 10)->where('stock', '>', 0);
                break;
            case 'out':
                $query->where('stock', 0);
                break;
            case 'sufficient':
                $query->where('stock', '>=', 10);
                break;
        }

        return $query->get();
    }

    private function getCategoryDistribution()
    {
        return DB::table('products')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.name as category',
                DB::raw('COUNT(*) as product_count'),
                DB::raw('SUM(products.stock) as total_stock')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    private function getStockValue()
    {
        return Product::join('product_batches', 'products.id', '=', 'product_batches.product_id')
            ->select(
                DB::raw('SUM(product_batches.quantity * product_batches.cost_price) as total_cost_value'),
                DB::raw('SUM(product_batches.quantity * products.price) as total_retail_value')
            )
            ->first();
    }

    private function getProfitLossData($startDate, $endDate)
    {
        $revenue = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');

        $costOfGoods = DB::table('sale_items')
            ->join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->sum(DB::raw('sale_items.quantity * product_batches.cost_price'));

        $grossProfit = $revenue - $costOfGoods;
        $taxCollected = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('tax_amount');
        $discountGiven = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('discount_amount');

        return [
            'revenue' => $revenue,
            'cost_of_goods' => $costOfGoods,
            'gross_profit' => $grossProfit,
            'tax_collected' => $taxCollected,
            'discount_given' => $discountGiven,
            'net_profit' => $grossProfit - $discountGiven
        ];
    }

    private function getRevenueExpensesTrend($startDate, $endDate)
    {
        return Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_amount) as revenue'),
            DB::raw('SUM(tax_amount) as tax'),
            DB::raw('SUM(discount_amount) as discount')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getProfitByCategory($startDate, $endDate)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'categories.name as category',
                DB::raw('SUM(sale_items.total_price) as revenue'),
                DB::raw('SUM(sale_items.quantity * product_batches.cost_price) as cost'),
                DB::raw('SUM(sale_items.total_price - (sale_items.quantity * product_batches.cost_price)) as profit')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    private function getExpiringProducts($days)
    {
        return ProductBatch::with('product')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($batch) {
                $batch->days_until_expiry = now()->diffInDays($batch->expiry_date, false);
                return $batch;
            });
    }

    private function getExpiryTrend()
    {
        return ProductBatch::select(
            DB::raw('MONTH(expiry_date) as month'),
            DB::raw('YEAR(expiry_date) as year'),
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('COUNT(*) as batch_count')
        )
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addYear())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function getCashierPerformance($startDate, $endDate, $cashierId = null)
    {
        $query = User::whereHas('sales', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        });

        // Filter by specific cashier if provided
        if ($cashierId) {
            $query->where('id', $cashierId);
        }

        return $query->withCount(['sales as total_sales' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['sales as total_revenue' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_amount')
            ->get()
            ->map(function ($user) {
                $user->average_sale = $user->total_sales > 0 ? $user->total_revenue / $user->total_sales : 0;
                return $user;
            });
    }

    private function getCashierTrends($startDate, $endDate, $cashierId = null)
    {
        $query = Sale::select(
            'cashier_id',
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('cashier_id');

        // Filter by specific cashier if provided
        if ($cashierId) {
            $query->where('cashier_id', $cashierId);
        }

        return $query->groupBy('cashier_id', 'date')
            ->orderBy('date')
            ->get();
    }

    private function getCategorySales($startDate, $endDate)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'categories.name as category',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total_price) as revenue'),
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    private function getCategoryTrend($startDate, $endDate)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'categories.name as category',
                DB::raw('DATE(sales.created_at) as date'),
                DB::raw('SUM(sale_items.total_price) as revenue')
            )
            ->groupBy('categories.id', 'categories.name', 'date')
            ->orderBy('date')
            ->get();
    }

    private function getDailySales($date)
    {
        return Sale::with(['items.product', 'cashier'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at')
            ->get();
    }

    private function getHourlySales($date)
    {
        return Sale::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(total_amount) as revenue')
        )
            ->whereDate('created_at', $date)
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    private function getTopProductsDaily($date)
    {
        return DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.created_at', $date)
            ->select(
                'products.name',
                'categories.name as category',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total_price) as revenue')
            )
            ->groupBy('sale_items.product_id', 'products.name', 'categories.name')
            ->orderBy('quantity_sold', 'desc')
            ->limit(10)
            ->get();
    }

    private function getMovementData($productIds)
    {
        if (empty($productIds)) {
            return [];
        }

        // Get sales data for last 30 days
        $salesData = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereIn('sale_items.product_id', $productIds)
            ->where('sales.created_at', '>=', now()->subDays(30))
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as transaction_count')
            )
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');

        // Categorize movement
        $movementData = [];
        foreach ($productIds as $productId) {
            $sold = $salesData[$productId]->total_sold ?? 0;

            if ($sold >= 50) {
                $movement = 'fast-moving';
            } elseif ($sold >= 20) {
                $movement = 'medium-moving';
            } elseif ($sold > 0) {
                $movement = 'slow-moving';
            } else {
                $movement = 'non-moving';
            }

            $movementData[$productId] = [
                'movement' => $movement,
                'total_sold' => $sold,
                'transaction_count' => $salesData[$productId]->transaction_count ?? 0
            ];
        }

        return $movementData;
    }

    public function exportInventoryReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $filters = json_decode($request->input('filters', '{}'), true);

        // Build query with filters (same as inventory method)
        $query = Product::with(['category', 'batches']);

        // Apply filters (reuse filter logic)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['barcode'])) {
            $query->where('barcode', 'like', '%' . $filters['barcode'] . '%');
        }

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['brand']) && $filters['brand'] !== 'all') {
            $query->where('brand', $filters['brand']);
        }

        if (!empty($filters['stock_status']) && $filters['stock_status'] !== 'all') {
            $settings = \App\Models\SystemSetting::getSettings();
            switch ($filters['stock_status']) {
                case 'in-stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'out-of-stock':
                    $query->where('stock', '<=', 0);
                    break;
                case 'low-stock':
                    $query->where('stock', '>', 0)
                          ->where('stock', '<=', $settings->low_stock_threshold);
                    break;
            }
        }

        $sortBy = $filters['sort_by'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $query->orderBy($sortBy, $sortDir);

        $products = $query->get();

        switch ($exportType) {
            case 'excel':
                return Excel::download(new \App\Exports\InventoryExport($products, $filters), 'inventory-report-' . now()->format('Y-m-d') . '.xlsx');

            case 'csv':
                return Excel::download(new \App\Exports\InventoryExport($products, $filters), 'inventory-report-' . now()->format('Y-m-d') . '.csv', \Maatwebsite\Excel\Excel::CSV);

            case 'pdf':
                return Excel::download(new \App\Exports\InventoryExport($products, $filters), 'inventory-report-' . now()->format('Y-m-d') . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);

            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    public function exportProfitLossReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $profitLossData = $this->getProfitLossData($startDate, $endDate);
        $profitByCategory = $this->getProfitByCategory($startDate, $endDate);

        $fileName = 'profit-loss-report-' . $startDate . '-to-' . $endDate;

        switch ($exportType) {
            case 'excel':
                return Excel::download(
                    new \App\Exports\ProfitLossExport($profitLossData, $profitByCategory, $startDate, $endDate),
                    $fileName . '.xlsx'
                );

            case 'csv':
                return Excel::download(
                    new \App\Exports\ProfitLossExport($profitLossData, $profitByCategory, $startDate, $endDate),
                    $fileName . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );

            case 'pdf':
                return Excel::download(
                    new \App\Exports\ProfitLossExport($profitLossData, $profitByCategory, $startDate, $endDate),
                    $fileName . '.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );

            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    public function exportExpiringProductsReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $filters = $request->only([
            'expiry_within_days', 'expiry_start', 'expiry_end', 'expired_only',
            'batch_number', 'manufacture_start', 'manufacture_end', 'batch_status',
            'product_name', 'barcode', 'category_id', 'brand', 'generic_name',
            'supplier_id', 'stock_status', 'sort_by', 'sort_dir'
        ]);

        // Set defaults
        $filters['expiry_within_days'] = (int) ($filters['expiry_within_days'] ?? 30);
        $filters['expired_only'] = (bool) ($filters['expired_only'] ?? false);
        $filters['batch_status'] = $filters['batch_status'] ?? 'active';
        $filters['sort_by'] = $filters['sort_by'] ?? 'expiry_date';
        $filters['sort_dir'] = $filters['sort_dir'] ?? 'asc';
        $filters['stock_status'] = $filters['stock_status'] ?? 'in-stock';

        // Build query (same as expiringProducts method)
        $query = ProductBatch::with(['product.category', 'product.batches'])
            ->join('products', 'product_batches.product_id', '=', 'products.id');

        // Apply filters
        if ($filters['expired_only']) {
            $query->where('product_batches.expiry_date', '<', now());
        } else {
            if (!empty($filters['expiry_start'])) {
                $query->where('product_batches.expiry_date', '>=', $filters['expiry_start']);
            } else {
                $query->where('product_batches.expiry_date', '>=', now());
            }

            if (!empty($filters['expiry_end'])) {
                $query->where('product_batches.expiry_date', '<=', $filters['expiry_end']);
            } elseif (!empty($filters['expiry_within_days'])) {
                $query->where('product_batches.expiry_date', '<=', now()->addDays($filters['expiry_within_days']));
            }
        }

        if (!empty($filters['batch_number'])) {
            $query->where('product_batches.batch_number', 'like', '%' . $filters['batch_number'] . '%');
        }

        if (!empty($filters['manufacture_start'])) {
            $query->where('product_batches.manufacturing_date', '>=', $filters['manufacture_start']);
        }

        if (!empty($filters['manufacture_end'])) {
            $query->where('product_batches.manufacturing_date', '<=', $filters['manufacture_end']);
        }

        if ($filters['batch_status'] === 'active') {
            $query->where('product_batches.quantity', '>', 0);
        } elseif ($filters['batch_status'] === 'inactive') {
            $query->where('product_batches.quantity', '=', 0);
        }

        if (!empty($filters['product_name'])) {
            $query->where('products.name', 'like', '%' . $filters['product_name'] . '%');
        }

        if (!empty($filters['barcode'])) {
            $query->where('products.barcode', 'like', '%' . $filters['barcode'] . '%');
        }

        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['brand'])) {
            $query->where('products.brand', 'like', '%' . $filters['brand'] . '%');
        }

        if (!empty($filters['generic_name'])) {
            $query->where('products.generic_name', 'like', '%' . $filters['generic_name'] . '%');
        }

        if (!empty($filters['supplier_id'])) {
            $query->whereExists(function($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('purchase_order_items')
                    ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                    ->whereColumn('purchase_order_items.product_id', 'products.id')
                    ->where('purchase_orders.supplier_id', $filters['supplier_id']);
            });
        }

        if ($filters['stock_status'] === 'in-stock') {
            $query->where('products.stock', '>', 0);
        } elseif ($filters['stock_status'] === 'zero-stock') {
            $query->where('products.stock', '=', 0);
        } elseif ($filters['stock_status'] === 'low-stock') {
            $query->where('products.stock', '>', 0)
                  ->where('products.stock', '<', 10);
        }

        $sortColumn = match($filters['sort_by']) {
            'product_name' => 'products.name',
            'batch_number' => 'product_batches.batch_number',
            default => 'product_batches.expiry_date'
        };

        $query->orderBy($sortColumn, $filters['sort_dir']);
        $query->select('product_batches.*');

        $expiringProducts = $query->get();

        // Add days until expiry
        $expiringProducts->transform(function ($batch) {
            $batch->days_until_expiry = now()->diffInDays($batch->expiry_date, false);
            return $batch;
        });

        $fileName = 'expiring-products-' . now()->format('Y-m-d');

        switch ($exportType) {
            case 'excel':
                return Excel::download(
                    new \App\Exports\ExpiringProductsExport($expiringProducts, $filters),
                    $fileName . '.xlsx'
                );

            case 'csv':
                return Excel::download(
                    new \App\Exports\ExpiringProductsExport($expiringProducts, $filters),
                    $fileName . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );

            case 'pdf':
                return Excel::download(
                    new \App\Exports\ExpiringProductsExport($expiringProducts, $filters),
                    $fileName . '.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );

            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    public function exportSalesTrendsReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $period = $request->input('period', 'monthly');
        $days = $request->input('days', 30);

        $salesData = $this->getSalesTrendsData($period, $days);
        $topProducts = $this->getTopProducts(10);
        $paymentMethods = $this->getPaymentMethodDistribution();

        $fileName = 'sales-trends-' . $period . '-' . now()->format('Y-m-d');

        return match ($exportType) {
            'excel' => \Excel::download(
                new \App\Exports\SalesTrendsExport($salesData, $topProducts, $paymentMethods, $period, $days),
                $fileName . '.xlsx'
            ),
            'csv' => \Excel::download(
                new \App\Exports\SalesTrendsExport($salesData, $topProducts, $paymentMethods, $period, $days),
                $fileName . '.csv',
                \Maatwebsite\Excel\Excel::CSV
            ),
            'pdf' => \Excel::download(
                new \App\Exports\SalesTrendsExport($salesData, $topProducts, $paymentMethods, $period, $days),
                $fileName . '.pdf',
                \Maatwebsite\Excel\Excel::DOMPDF
            ),
            default => back()->with('error', 'Invalid export type'),
        };
    }

    public function exportSalesByCashierReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $cashierId = $request->input('cashier_id');

        $cashierPerformance = $this->getCashierPerformance($startDate, $endDate, $cashierId);

        // Get cashier name if specific cashier is selected
        $cashierName = null;
        if ($cashierId) {
            $cashier = User::find($cashierId);
            $cashierName = $cashier ? $cashier->name : null;
        }

        $fileName = 'sales-by-cashier-' . now()->format('Y-m-d');

        switch ($exportType) {
            case 'excel':
                return Excel::download(
                    new \App\Exports\SalesByCashierExport($cashierPerformance, $startDate, $endDate, $cashierName),
                    $fileName . '.xlsx'
                );

            case 'csv':
                return Excel::download(
                    new \App\Exports\SalesByCashierExport($cashierPerformance, $startDate, $endDate, $cashierName),
                    $fileName . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );

            case 'pdf':
                return Excel::download(
                    new \App\Exports\SalesByCashierExport($cashierPerformance, $startDate, $endDate, $cashierName),
                    $fileName . '.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );

            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    public function exportDailySalesReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $date = $request->input('date', now()->format('Y-m-d'));

        $dailySales = $this->getDailySales($date);

        $fileName = 'daily-sales-' . $date;

        switch ($exportType) {
            case 'excel':
                return Excel::download(
                    new \App\Exports\DailySalesExport($dailySales, $date),
                    $fileName . '.xlsx'
                );

            case 'csv':
                return Excel::download(
                    new \App\Exports\DailySalesExport($dailySales, $date),
                    $fileName . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );

            case 'pdf':
                return Excel::download(
                    new \App\Exports\DailySalesExport($dailySales, $date),
                    $fileName . '.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );

            default:
                return back()->with('error', 'Invalid export type');
        }
    }

    public function exportSalesByCategoryReport(Request $request)
    {
        $exportType = $request->input('export_type', 'excel');
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $categorySales = $this->getCategorySales($startDate, $endDate);

        $fileName = 'sales-by-category-' . now()->format('Y-m-d');

        switch ($exportType) {
            case 'excel':
                return Excel::download(
                    new \App\Exports\SalesByCategoryExport($categorySales, $startDate, $endDate),
                    $fileName . '.xlsx'
                );

            case 'csv':
                return Excel::download(
                    new \App\Exports\SalesByCategoryExport($categorySales, $startDate, $endDate),
                    $fileName . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );

            case 'pdf':
                return Excel::download(
                    new \App\Exports\SalesByCategoryExport($categorySales, $startDate, $endDate),
                    $fileName . '.pdf',
                    \Maatwebsite\Excel\Excel::DOMPDF
                );

            default:
                return back()->with('error', 'Invalid export type');
        }
    }
}
