<?php

namespace App\Providers;

use App\Models\ReturnOrder;
use App\Models\Sale;
use App\Observers\TransactionObserver;
use Illuminate\Auth\Access\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        $this->registerModelObservers();

        // Register event listeners
        $this->registerEventListeners();

        // Register global transaction handler
//        $this->registerTransactionHandler();

        Blade::if('hasPermission', function ($permission) {
            $user = Auth::user();

            if (!$user) {
                return false;
            }

            $role = $user->role;

            if (!$role || !isset($role->permissions)) {
                return false;
            }

            if (is_array($role->permissions)) {
                $permissions = $role->permissions;
            } else {
                $permissions = json_decode($role->permissions, true) ?? [];
            }

            // Handle wildcard permission
            if (is_array($permissions) && in_array("*", $permissions)) {
                return true;
            }

            // Check specific permission
            return in_array($permission, $permissions);
        });

        // Register permission middleware alias
        Route::aliasMiddleware('can', \App\Http\Middleware\CheckPermission::class);
        Route::aliasMiddleware('role', \App\Http\Middleware\CheckRole::class);

        // Register custom Blade directive for permission checks
        \Illuminate\Support\Facades\Blade::directive('canPermission', function ($permission) {
            return "<?php if(auth()->check() && auth()->user()->hasPermission({$permission})): ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('endcanPermission', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * Register model observers for automatic transaction creation.
     */
    protected function registerModelObservers(): void
    {
        // Option 1: Using Eloquent Observers (if you create separate observer classes)
        // Sale::observe(SaleObserver::class);
        // OrderReturn::observe(ReturnObserver::class);
        // Expense::observe(ExpenseObserver::class);
        // Payment::observe(PaymentObserver::class);

        // Option 2: Using events (more flexible, recommended)
        // We'll use event listeners instead
    }

    /**
     * Register event listeners for transaction creation.
     */
    protected function registerEventListeners(): void
    {
        // Listen for Sale events
        Event::listen('eloquent.created: ' . Sale::class, function ($sale) {
            // Only create transaction after database transaction is committed
            if (app()->runningInConsole() || !app()->runningUnitTests()) {
                TransactionObserver::handleSaleCreated($sale);
            }
        });

        Event::listen('eloquent.updated: ' . Sale::class, function ($sale) {
            TransactionObserver::handleSaleUpdated($sale);
        });

        // Listen for OrderReturn events
        Event::listen('eloquent.created: ' . ReturnOrder::class, function ($orderReturn) {
            TransactionObserver::handleReturnCreated($orderReturn);
        });

//        Event::listen('eloquent.updated: ' . OrderReturn::class, function ($orderReturn) {
//            TransactionObserver::handleReturnUpdated($orderReturn);
//        });

        // Listen for Expense events
//        Event::listen('eloquent.created: ' . Expense::class, function ($expense) {
//            TransactionObserver::handleExpenseCreated($expense);
//        });

        // Listen for Payment events
//        Event::listen('eloquent.created: ' . Payment::class, function ($payment) {
//            TransactionObserver::handlePaymentCreated($payment);
//        });

        // Custom events for manual transaction creation
        Event::listen('transaction.sale.created', function ($sale, $paymentMethod) {
            TransactionObserver::createSaleTransaction($sale, $paymentMethod);
        });

        Event::listen('transaction.refund.created', function ($orderReturn, $paymentMethod) {
            TransactionObserver::createRefundTransaction($orderReturn, $paymentMethod);
        });

        Event::listen('transaction.expense.created', function ($expense) {
            TransactionObserver::createExpenseTransaction($expense);
        });

        Event::listen('transaction.payment.created', function ($payment) {
            TransactionObserver::createPaymentTransaction($payment);
        });

        Event::listen('transaction.direct.created', function ($data) {
            TransactionObserver::createDirectTransaction($data);
        });
    }

    /**
     * Register a global transaction handler to ensure transactions are created
     * only after the database transaction is committed.
     */
//    protected function registerTransactionHandler(): void
//    {
//        // This ensures transaction records are only created after successful commit
//        Event::listen(TransactionCommitted::class, function ($event) {
//            // You can add any post-commit logic here
//            // For example, send notifications, update caches, etc.
//        });
//    }

    /**
     * Register helper functions for manual transaction creation.
     */
    protected function registerHelpers(): void
    {
        // Register global helper functions (optional)
        if (!function_exists('create_sale_transaction')) {
            function create_sale_transaction($sale, $paymentMethod = 'cash') {
                return \App\Observers\TransactionObserver::createSaleTransaction($sale, $paymentMethod);
            }
        }

        if (!function_exists('create_refund_transaction')) {
            function create_refund_transaction($orderReturn, $paymentMethod = 'cash') {
                return \App\Observers\TransactionObserver::createRefundTransaction($orderReturn, $paymentMethod);
            }
        }

        if (!function_exists('create_expense_transaction')) {
            function create_expense_transaction($expense) {
                return \App\Observers\TransactionObserver::createExpenseTransaction($expense);
            }
        }

        if (!function_exists('create_payment_transaction')) {
            function create_payment_transaction($payment) {
                return \App\Observers\TransactionObserver::createPaymentTransaction($payment);
            }
        }

        if (!function_exists('create_direct_transaction')) {
            function create_direct_transaction(array $data) {
                return \App\Observers\TransactionObserver::createDirectTransaction($data);
            }
        }
    }
}
