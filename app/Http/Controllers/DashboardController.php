<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Sales Statistics
        $todaySales = Sale::whereDate('created_at', today())->sum('total_amount');
        $yesterdaySales = Sale::whereDate('created_at', today()->subDay())->sum('total_amount');
        $monthSales = Sale::whereMonth('created_at', now()->month)->sum('total_amount');
        $lastMonthSales = Sale::whereMonth('created_at', now()->subMonth()->month)->sum('total_amount');

        // Calculate percentage changes
        $todayChange = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;
        $monthChange = $lastMonthSales > 0 ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;
        $monthChange = min($monthChange, 100);
        $todayChange = min($todayChange, 100);

        // Product Statistics
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<', 10)->where('stock', '>', 0)->count();
        $outOfStockProducts = Product::where('stock', 0)->count();
        $activeProducts = Product::where('is_active', true)->count();

        // Expiry Statistics
        $expiringSoon = ProductBatch::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->where('quantity', '>', 0)
            ->limit(5)
            ->count();

        // Recent Data
        $recentSales = Sale::with('items.product')
            ->latest()
            ->limit(5)
            ->get();

        $recentProducts = Product::with('category')
            ->latest()
            ->limit(5)
            ->get();

        $lowStockAlerts = Product::with('category')
            ->where('stock', '<', 10)
//            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $expiringProducts = ProductBatch::with('product')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();

        $currency_symbol = get_currency_symbol();


        return view('dashboard', compact(
            'todaySales',
            'todayChange',
            'monthSales',
            'monthChange',
            'totalProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'activeProducts',
            'expiringSoon',
            'recentSales',
            'recentProducts',
            'lowStockAlerts',
            'expiringProducts',
            'currency_symbol'
        ));
    }
}
