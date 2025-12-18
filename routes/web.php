<?php

use App\Http\Controllers\Api\Pos\BatchAllocationController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PointOfSaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnOrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// In routes/web.php - add a test route
//Route::get('/test', function () {
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
//});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

// POS Routes
Route::prefix('pos')->middleware(['auth', 'can:pos.view'])->group(function () {
    Route::get('/', [PointOfSaleController::class, 'index'])->name('pos.index');
    Route::get('/search', [PointOfSaleController::class, 'searchProducts'])->name('pos.search');
    Route::post('/add-to-cart', [PointOfSaleController::class, 'addToCart'])->name('pos.add-to-cart');
});

// Categories Routes
Route::prefix('categories')->middleware(['auth'])->group(function () {
    Route::middleware(['can:categories.create'])->group(function () {
        Route::get('/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/category/store', [CategoryController::class, 'store'])->name('categories.store');
    });

    Route::middleware(['can:categories.view'])->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('/api/list', [CategoryController::class, 'getCategoriesJson'])->name('categories.api.list');
    });


    Route::middleware(['can:categories.edit'])->group(function () {
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('categories.update');
    });

    Route::middleware(['can:categories.delete'])->group(function () {
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });
});

// Inventory Routes
Route::prefix('inventory')->middleware(['auth'])->group(function () {
    Route::middleware(['can:inventory.view'])->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/{product}/batches', [InventoryController::class, 'batchManagement'])->name('inventory.batches.manage');
        Route::get('/api/{product_id}/batches/list', [InventoryController::class, 'getBatchesJson'])->name('inventory.api.batches.list');
    });

    Route::middleware(['can:inventory.create'])->group(function () {
        Route::get('/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/', [InventoryController::class, 'store'])->name('inventory.store');
        Route::post('/{product}/batches', [InventoryController::class, 'addBatch'])->name('inventory.batches.store');
    });

    Route::middleware(['can:inventory.edit'])->group(function () {
        Route::get('/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    });

    Route::middleware(['can:inventory.delete'])->group(function () {
        Route::delete('/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::delete('/batches/{batch}', [InventoryController::class, 'deleteBatch'])->name('inventory.batches.destroy');
    });
});

// Sales Routes
Route::prefix('sales')->middleware(['auth'])->group(function () {
    Route::middleware(['can:sales.view'])->group(function () {
        Route::get('/', [SalesController::class, 'index'])->name('sales.index');
        Route::get('/{sale}', [SalesController::class, 'show'])->name('sales.show');
        Route::get('/{sale}/print', [SalesController::class, 'printInvoice'])->name('sales.print');
        Route::get('/export/excel', [SalesController::class, 'exportExcel'])->name('sales.export.excel');
        Route::get('/export/pdf', [SalesController::class, 'exportPDF'])->name('sales.export.pdf');
    });

    Route::middleware(['can:sales.create'])->group(function () {
        Route::post('/complete', [SalesController::class, 'completeSale'])->name('sales.complete');
    });

    Route::middleware(['can:sales.delete'])->group(function () {
        Route::delete('/{sale}', [SalesController::class, 'destroy'])->name('sales.destroy');
    });
});

// POS Routes with hold functionality
Route::prefix('pos')->middleware(['auth', 'can:pos.view'])->group(function () {
    Route::post('/hold-sale', [PointOfSaleController::class, 'holdSale'])->name('pos.hold-sale');
    Route::post('/release-sale/{holdId}', [PointOfSaleController::class, 'releaseSale'])->name('pos.release-sale');
    Route::delete('/delete-held-sale/{holdId}', [PointOfSaleController::class, 'deleteHeldSale'])->name('pos.delete-held-sale');
    Route::get('/held-sales', [PointOfSaleController::class, 'getHeldSales'])->name('pos.held-sales');
});

// Reports Routes
Route::prefix('reports')->middleware(['auth', 'can:reports.view'])->group(function () {
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
    /*Route::middleware(['can:settings.view'])->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
    });*/

    // System settings edit access
//    Route::middleware(['can:settings.edit'])->group(function () {
    Route::prefix('')->group(function () {
        Route::get('/system', [SettingsController::class, 'system'])->name('settings.system')->middleware('can:settings.view');
        Route::put('/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update')->middleware('can:settings.edit');
    });

    // Roles and permissions management
    Route::middleware(['can:roles.view'])->group(function () {
        Route::get('/roles', [SettingsController::class, 'roles'])->name('settings.roles');
        Route::get('/roles/{role}/permissions', [SettingsController::class, 'getRolePermissions'])->name('settings.roles.permissions');
    });

    Route::middleware(['can:roles.create'])->group(function () {
        Route::post('/roles', [SettingsController::class, 'storeRole'])->name('settings.roles.store');
    });

    Route::middleware(['can:roles.edit'])->group(function () {
        Route::put('/roles/{role}', [SettingsController::class, 'updateRole'])->name('settings.roles.update');
        Route::put('/roles/{role}/permissions', [SettingsController::class, 'updateRolePermissions'])->name('settings.roles.permissions.update');
    });

    Route::middleware(['can:roles.delete'])->group(function () {
        Route::delete('/roles/{role}', [SettingsController::class, 'destroyRole'])->name('settings.roles.destroy');
    });

    // User management access
    Route::middleware(['can:users.view'])->group(function () {
        Route::get('/users', [SettingsController::class, 'users'])->name('settings.users');
    });

    Route::middleware(['can:users.create'])->group(function () {
        Route::post('/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    });

    Route::middleware(['can:users.edit'])->group(function () {
        Route::put('/users/{user}', [SettingsController::class, 'updateUser'])->name('settings.users.update');
    });

    Route::middleware(['can:users.delete'])->group(function () {
        Route::delete('/users/{user}', [SettingsController::class, 'destroy'])->name('settings.users.destroy');
    });
});

// Purchase/Suppliers Routes
Route::prefix('purchases')->middleware(['auth'])->group(function () {
    Route::middleware(['can:purchases.create'])->group(function () {
        Route::get('/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::post('/{purchase}/order', [PurchaseController::class, 'markAsOrdered'])->name('purchases.mark-ordered');
        Route::post('/{purchase}/receive', [PurchaseController::class, 'receiveStore'])->name('purchases.receive-store');
    });

    // Supplier Routes - specific routes before parameterized routes
    Route::middleware(['can:suppliers.create'])->group(function () {
        Route::get('/suppliers/create', [PurchaseController::class, 'createSupplier'])->name('purchases.suppliers.create');
        Route::post('/suppliers', [PurchaseController::class, 'storeSupplier'])->name('purchases.suppliers.store');
    });

    // Purchase return Routes
    Route::middleware(['can:suppliers.return'])->prefix('returns')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('purchases.returns.index');
        Route::get('/create', [PurchaseReturnController::class, 'create'])->name('purchases.returns.create');
        Route::post('/', [PurchaseReturnController::class, 'store'])->name('purchases.returns.store');
        Route::get('/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchases.returns.show');
        Route::get('/{purchaseReturn}/edit', [PurchaseReturnController::class, 'edit'])->name('purchases.returns.edit');
        Route::put('/{purchaseReturn}', [PurchaseReturnController::class, 'update'])->name('purchases.returns.update');
        Route::delete('/{purchaseReturn}', [PurchaseReturnController::class, 'destroy'])->name('purchases.returns.destroy');

        Route::post('/{purchaseReturn}/approve', [PurchaseReturnController::class, 'approve'])->name('purchases.returns.approve');
        Route::post('/{purchaseReturn}/reject', [PurchaseReturnController::class, 'reject'])->name('purchases.returns.reject');
        Route::post('/{purchaseReturn}/complete', [PurchaseReturnController::class, 'complete'])->name('purchases.returns.complete');
    });

    Route::middleware(['can:suppliers.view'])->group(function () {
        Route::get('/suppliers', [PurchaseController::class, 'suppliers'])->name('purchases.suppliers.index');
        Route::get('/suppliers/{supplier}', [PurchaseController::class, 'showSupplier'])->name('purchases.suppliers.show');
    });

    Route::middleware(['can:purchases.view'])->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::get('/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    });


    Route::middleware(['can:purchases.edit'])->group(function () {
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update');
    });

    Route::middleware(['can:purchases.delete'])->group(function () {
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });



    Route::middleware(['can:suppliers.edit'])->group(function () {
        Route::get('/suppliers/{supplier}/edit', [PurchaseController::class, 'editSupplier'])->name('purchases.suppliers.edit');
        Route::put('/suppliers/{supplier}', [PurchaseController::class, 'updateSupplier'])->name('purchases.suppliers.update');
    });

    Route::middleware(['can:suppliers.delete'])->group(function () {
        Route::delete('/suppliers/{supplier}', [PurchaseController::class, 'destroySupplier'])->name('purchases.suppliers.destroy');
    });
});

// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::prefix('backups')->middleware(['can:settings.edit'])->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/create', [BackupController::class, 'create'])->name('backups.create');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/delete/{filename}', [BackupController::class, 'delete'])->name('backups.delete');
    });
});

Route::middleware(['auth'])->group(function () {
    // Search invoice page
    Route::get('/returns/search', [ReturnOrderController::class, 'search'])->name('returns.search')->middleware('can:returns.view');
    Route::get('/returns/results', [ReturnOrderController::class, 'search_result'])->name('returns.results');

    Route::get('/returns', [ReturnOrderController::class, 'index'])->name('returns.index')->middleware('can:returns.view');
    Route::get('/returns/create/{sale}', [ReturnOrderController::class, 'create'])->name('returns.create')->middleware('can:returns.create');
    Route::post('/returns/{sale}', [ReturnOrderController::class, 'store'])->name('returns.store')->middleware('can:returns.store');
    Route::get('/returns/{returnOrder}', [ReturnOrderController::class, 'show'])->name('returns.show');
    Route::post('/returns/{return}/approve', [ReturnOrderController::class, 'approve'])->name('returns.approve')->middleware('can:returns.approve');
    Route::post('/returns/{return}/reject', [ReturnOrderController::class, 'reject'])->name('returns.reject')->middleware('can:returns.approve');
    Route::post('/returns/{return}/cancel', [ReturnOrderController::class, 'cancel'])->name('returns.cancel')->middleware('can:returns.cancel');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::resource('/transactions', TransactionController::class)->middleware('can:transactions.view');
//    Route::get('/transactions/statistics', [TransactionController::class, 'statistics'])->name('transactions.statistics')->middleware('can:transactions.view');
});

// Mimic api routes
Route::middleware(['auth'])->prefix('api')->group(function () {
    Route::post('/pos/allocate-batches', [BatchAllocationController::class, 'allocate'])->name('pos.allocate.batch');

    // Returnable items
    Route::get('/purchase-orders/{purchaseOrder}/returnable-items', [PurchaseReturnController::class, 'getReturnableItems']);
});

require __DIR__ . '/auth.php';
