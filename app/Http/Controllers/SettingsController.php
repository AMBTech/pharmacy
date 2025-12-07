<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\UserRole;
use App\Models\User;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $allowed = [
            'tax_rate',
            'currency',
            'low_stock_threshold',
        ];

        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'currency' => 'required|string|max:10',
            'low_stock_threshold' => 'required|integer|min:1',
            'company_name' => 'nullable|string|max:255',
//            'company_address' => 'nullable|string',
//            'company_phone' => 'nullable|string|max:20',
//            'license_number' => 'nullable|string|max:20',
//            'company_email' => 'nullable|email|max:255'
        ]);

//        $validated = Arr::only($validated, $allowed);

        $settings = SystemSetting::getSettings();
        $settings->update($validated);

        return redirect()->route('settings.system')
            ->with('success', 'System settings updated successfully.');
    }

    private function authorize($permission)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission($permission)) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()
                    ->route('settings.system')
                    ->with('error', 'You do not have permission to update system settings.')
            );
        }
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
        $permissions = PermissionHelper::getGroupedPermissions();

        return view('settings.roles', compact('roles', 'permissions'));
    }

    public function createRole()
    {
        $permissions = PermissionHelper::getGroupedPermissions();
        return view('settings.roles.create', compact('permissions'));
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_roles,name|regex:/^[a-z0-9_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ], [
            'name.regex' => 'The name field must contain only lowercase letters, numbers, and underscores.'
        ]);

        $role = UserRole::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
            'is_system' => false
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'role' => $role
            ]);
        }

        return redirect()->route('settings.roles')
            ->with('success', 'Role created successfully.');
    }

    public function editRole(UserRole $role)
    {
        $permissions = PermissionHelper::getGroupedPermissions();
        return view('settings.roles.edit', compact('role', 'permissions'));
    }

    public function updateRole(Request $request, UserRole $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_roles,name,' . $role->id . '|regex:/^[a-z0-9_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ], [
            'name.regex' => 'The name field must contain only lowercase letters, numbers, and underscores.'
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? []
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'role' => $role
            ]);
        }

        return redirect()->route('settings.roles')
            ->with('success', 'Role updated successfully.');
    }

    public function updateRolePermissions(Request $request, UserRole $role)
    {
        $validated = $request->validate([
            'permissions' => 'nullable|array'
        ]);

        $role->update([
            'permissions' => $validated['permissions'] ?? []
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role permissions updated successfully.',
                'role' => $role
            ]);
        }

        return redirect()->route('settings.roles')
            ->with('success', 'Role permissions updated successfully.');
    }

    public function destroyRole(UserRole $role)
    {
        // Prevent deleting system roles
        if ($role->is_system) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete system roles.'
                ], 403);
            }
            return redirect()->route('settings.roles')
                ->with('error', 'Cannot delete system roles.');
        }

        // Prevent deleting roles that have users
        if ($role->users()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role with assigned users. Please reassign users first.'
                ], 403);
            }
            return redirect()->route('settings.roles')
                ->with('error', 'Cannot delete role with assigned users. Please reassign users first.');
        }

        $role->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.'
            ]);
        }

        return redirect()->route('settings.roles')
            ->with('success', 'Role deleted successfully.');
    }

    public function getRolePermissions(UserRole $role)
    {
        return response()->json([
            'success' => true,
            'permissions' => $role->permissions ?? []
        ]);
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
