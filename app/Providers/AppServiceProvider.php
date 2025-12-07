<?php

namespace App\Providers;

use Illuminate\Auth\Access\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
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
}
