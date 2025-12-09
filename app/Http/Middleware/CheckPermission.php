<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        // If user not authenticated: redirect to login for web, JSON for API
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // you may want to change 'login' to your actual named login route
            return redirect()->guest(route('login'))
                ->with('error', 'Please login to continue.');
        }

        // If user doesn't have the required permission:
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            // JSON / AJAX clients should still receive HTTP 403
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            // For normal web requests, redirect back to settings page with error
            // You can change the route to a more generic location if needed.
            return redirect()
                ->back()
                ->with('error', 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
