<?php

namespace App\Helpers;

class PermissionHelper
{
    /**
     * Get all available permissions
     */
    public static function getAllPermissions(): array
    {
        $permissions = [];
        foreach (config('permissions.modules') as $module => $data) {
            foreach ($data['permissions'] as $action => $label) {
                $permissions[] = "$module.$action";
            }
        }
        return $permissions;
    }

    /**
     * Get permissions grouped by module
     */
    public static function getGroupedPermissions(): array
    {
        $grouped = [];
        foreach (config('permissions.modules') as $module => $data) {
            $grouped[$module] = [
                'label' => $data['label'],
                'description' => $data['description'],
                'permissions' => []
            ];
            
            foreach ($data['permissions'] as $action => $label) {
                $grouped[$module]['permissions'][] = [
                    'key' => "$module.$action",
                    'action' => $action,
                    'label' => $label,
                ];
            }
        }
        return $grouped;
    }

    /**
     * Get permission modules configuration
     */
    public static function getModules(): array
    {
        return config('permissions.modules', []);
    }

    /**
     * Validate if permission exists
     */
    public static function exists(string $permission): bool
    {
        return in_array($permission, self::getAllPermissions());
    }

    /**
     * Get navigation items filtered by user permissions
     */
    public static function filterNavigation(array $navigation, $user): array
    {
        if (!$user || !$user->role) {
            return [];
        }

        // Admin with wildcard gets everything
        if ($user->hasPermission('*')) {
            return $navigation;
        }

        return array_filter($navigation, function($item) use ($user) {
            // If no permission specified, show the item
            if (!isset($item['permission'])) {
                // For groups, check if user has any child permissions
                if (isset($item['type']) && $item['type'] === 'group' && isset($item['children'])) {
                    $item['children'] = array_filter($item['children'], function($child) use ($user) {
                        return !isset($child['permission']) || $user->hasPermission($child['permission']);
                    });
                    return count($item['children']) > 0;
                }
                return true;
            }

            // Check if user has the permission
            return $user->hasPermission($item['permission']);
        });
    }

    /**
     * Check if user has any of the given permissions
     */
    public static function hasAny($user, array $permissions): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public static function hasAll($user, array $permissions): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }
}
