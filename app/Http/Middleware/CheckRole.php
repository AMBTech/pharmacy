<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = Auth()->user();
        if (!$this->hasPermission($user, $permission)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }

    private function hasPermission($user, $permission)
    {
        $role = $user->role;

        if (!$role || !isset($role->permissions)) {
            return false;
        }

        // Assuming permissions are stored as JSON in the database
        // Format: { "reports": ["view"], "products": ["view", "create"] }
        // check if $role->permissions is already an array
        if (is_array($role->permissions)) {
            $permissions = $role->permissions;
        } else {
            $permissions = json_decode($role->permissions, true) ?? [];
        }

        if (is_array($permission) && in_array("*", $permissions)) {
            return true;
        }

        // Check if user has the specific permission
        return in_array($permission, $permissions);

    }
}
