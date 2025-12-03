<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function system()
    {
        $settings = SystemSetting::getSettings();
        return view('settings.system', compact('settings'));
    }

    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|max:10',
            'low_stock_threshold' => 'required|integer|min:1',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255'
        ]);

        $settings = SystemSetting::getSettings();
        $settings->update($validated);

        return redirect()->route('settings.system')
            ->with('success', 'System settings updated successfully.');
    }

    public function users()
    {
        $users = User::with('role')->latest()->get();
        $roles = UserRole::all();
        return view('settings.users', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:user_roles,id'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id']
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.'
            ]);
        }

        return redirect()->route('settings.users')
            ->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'role_id' => 'required|exists:user_roles,id'
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id']
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.'
            ]);
        }

        return redirect()->route('settings.users')
            ->with('success', 'User updated successfully.');
    }

    public function roles()
    {
        $roles = UserRole::withCount('users')->get();
        $permissions = [
            'products' => ['view', 'create', 'edit', 'delete'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'sales' => ['view', 'create', 'edit', 'delete'],
            'reports' => ['view'],
            'settings' => ['view', 'edit'],
            'users' => ['view', 'create', 'edit', 'delete']
        ];

        return view('settings.roles', compact('roles', 'permissions'));
    }

    public function updateRolePermissions(Request $request, UserRole $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array'
        ]);

        $role->update([
            'permissions' => $validated['permissions'] ?? []
        ]);

        return redirect()->route('settings.roles')
            ->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);
        }

        return redirect()->route('settings.users')->with('success', 'User deleted successfully.');
    }
}
