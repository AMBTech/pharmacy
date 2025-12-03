<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PointOfSaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// In routes/web.php - add a test route
Route::get('/test', function () {
//    return view('purchases.suppliers');
//    $user = auth()->user();
//
//    if (!$user) {
//        return "No user logged in";
//    }
//
//    dd([
//        'user' => $user->name,
//        'role' => $user->role->name,
//        'permissions' => $user->role->permissions,
//        'has_settings.view' => $user->hasPermission('settings.view'),
//        'has_any_permission' => $user->hasPermission('*'),
//    ]);
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

// POS Routes
Route::prefix('pos')->middleware(['auth'])->group(function () {
    Route::get('/', [PointOfSaleController::class, 'index'])->name('pos.index');
    Route::get('/search', [PointOfSaleController::class, 'searchProducts'])->name('pos.search');
    Route::post('/add-to-cart', [PointOfSaleController::class, 'addToCart'])->name('pos.add-to-cart');
//    Route::post('/complete-sale', [PointOfSaleController::class, 'completeSale'])->name('pos.complete-sale');
});

// Categories Routes
Route::prefix('categories')->middleware(['auth'])->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // API routes for select dropdowns
    Route::get('/api/list', [CategoryController::class, 'getCategoriesJson'])->name('categories.api.list');
});

// Inventory Routes
Route::prefix('inventory')->middleware(['auth'])->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/{product}/batches', [InventoryController::class, 'addBatch'])->name('inventory.batches.store');
    Route::delete('/batches/{batch}', [InventoryController::class, 'deleteBatch'])->name('inventory.batches.destroy');
    Route::get('/{product}/batches', [InventoryController::class, 'batchManagement'])->name('inventory.batches.manage');
});

// Sales Routes
Route::prefix('sales')->middleware(['auth'])->group(function () {
    Route::get('/', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::post('/complete', [SalesController::class, 'completeSale'])->name('sales.complete');
    Route::get('/{sale}/print', [SalesController::class, 'printInvoice'])->name('sales.print');
    Route::delete('/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');

    // Export routes
    Route::get('/export/excel', [SalesController::class, 'exportExcel'])->name('sales.export.excel');
    Route::get('/export/pdf', [SalesController::class, 'exportPDF'])->name('sales.export.pdf');
});

// POS Routes with hold functionality
Route::prefix('pos')->middleware(['auth'])->group(function () {
    Route::get('/', [PointOfSaleController::class, 'index'])->name('pos.index');
    Route::post('/hold-sale', [PointOfSaleController::class, 'holdSale'])->name('pos.hold-sale');
    Route::post('/release-sale/{holdId}', [PointOfSaleController::class, 'releaseSale'])->name('pos.release-sale');
    Route::delete('/delete-held-sale/{holdId}', [PointOfSaleController::class, 'deleteHeldSale'])->name('pos.delete-held-sale');
    Route::get('/held-sales', [PointOfSaleController::class, 'getHeldSales'])->name('pos.held-sales');
});

// Reports Routes
Route::prefix('reports')->middleware(['auth'])->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/sales-trends', [ReportController::class, 'salesTrends'])->name('reports.sales-trends');
    Route::get('/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
    Route::get('/expiring-products', [ReportController::class, 'expiringProducts'])->name('reports.expiring-products');
    Route::get('/sales-by-cashier', [ReportController::class, 'salesByCashier'])->name('reports.sales-by-cashier');
    Route::get('/sales-by-category', [ReportController::class, 'salesByCategory'])->name('reports.sales-by-category');
    Route::get('/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily-sales');

    Route::post('/inventory/export', [ReportController::class, 'exportInventoryReport'])->name('reports.inventory.export');
    Route::post('/profit-loss/export', [ReportController::class, 'exportProfitLossReport'])->name('reports.profit-loss.export');
    Route::post('/expiring-products/export', [ReportController::class, 'exportExpiringProductsReport'])->name('reports.expiring-products.export');
    Route::post('/sales-trends/export', [ReportController::class, 'exportSalesTrendsReport'])->name('reports.sales-trends.export');
    Route::post('/sales-by-cashier/export', [ReportController::class, 'exportSalesByCashierReport'])->name('reports.sales-by-cashier.export');
    Route::post('/daily-sales/export', [ReportController::class, 'exportDailySalesReport'])->name('reports.daily-sales.export');
    Route::post('/sales-by-category/export', [ReportController::class, 'exportSalesByCategoryReport'])->name('reports.sales-by-category.export');
});

Route::middleware(['auth'])->prefix('settings')->group(function () {
    // Settings view access
//    Route::middleware(['permission:settings.view'])->group(function () {
    Route::middleware(['can:settings.view'])->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
    });

    // System settings edit access
    Route::middleware(['can:settings.edit'])->group(function () {
        Route::get('/system', [SettingsController::class, 'system'])->name('settings.system');
        Route::put('/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update');
        Route::get('/roles', [SettingsController::class, 'roles'])->name('settings.roles');
        Route::put('/roles/{role}/permissions', [SettingsController::class, 'updateRolePermissions'])->name('settings.roles.permissions.update');
    });

    // User management access
    Route::middleware(['can:users.view'])->group(function () {
        Route::get('/users', [SettingsController::class, 'users'])->name('settings.users');
    });

    Route::middleware(['can:users.create'])->group(function () {
        Route::post('/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
        Route::delete('/users/destroy', [SettingsController::class, 'destroy'])->name('settings.users.destroy');
    });

    Route::middleware(['can:users.edit'])->group(function () {
        Route::put('/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    });
});

// Purchase Routes
Route::middleware(['auth', \App\Http\Middleware\CheckPermission::class . ':purchases.view'])->prefix('purchases')->group(function () {
    Route::get('/suppliers', [PurchaseController::class, 'suppliers'])->name('purchases.suppliers.index');
    Route::get('/', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // Purchase Actions
    Route::post('/{purchase}/order', [PurchaseController::class, 'markAsOrdered'])->name('purchases.mark-ordered');
    Route::get('/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::post('/{purchase}/receive', [PurchaseController::class, 'receiveStore'])->name('purchases.receive-store');

    // Supplier Routes
    Route::get('/suppliers/create', [PurchaseController::class, 'createSupplier'])->name('purchases.suppliers.create');
    Route::post('/suppliers', [PurchaseController::class, 'storeSupplier'])->name('purchases.suppliers.store');
    Route::get('/suppliers/{supplier}', [PurchaseController::class, 'showSupplier'])->name('purchases.suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [PurchaseController::class, 'editSupplier'])->name('purchases.suppliers.edit');
    Route::put('/suppliers/{supplier}', [PurchaseController::class, 'updateSupplier'])->name('purchases.suppliers.update');
});

require __DIR__ . '/auth.php';
