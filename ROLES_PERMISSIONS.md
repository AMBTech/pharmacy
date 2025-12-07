# Dynamic Roles & Permissions System

This document explains the complete roles and permissions system implementation for the Pharmacy Management System.

## Overview

The system provides a flexible, permission-based access control where:
- Admins can create custom roles with specific permissions
- Sidebar menu items are automatically filtered based on user permissions
- All routes are protected with permission middleware
- System roles (admin, manager, cashier) cannot be deleted but can be modified

## Permission Structure

Permissions use dot notation: `module.action`

### Available Modules and Permissions:

1. **Dashboard**
   - `dashboard.view` - View dashboard

2. **Point of Sale**
   - `pos.view` - Access POS system

3. **Purchases**
   - `purchases.view` - View purchase orders
   - `purchases.create` - Create purchase orders
   - `purchases.edit` - Edit purchase orders
   - `purchases.delete` - Delete purchase orders

4. **Suppliers**
   - `suppliers.view` - View suppliers
   - `suppliers.create` - Create suppliers
   - `suppliers.edit` - Edit suppliers
   - `suppliers.delete` - Delete suppliers

5. **Inventory**
   - `inventory.view` - View inventory
   - `inventory.create` - Add products
   - `inventory.edit` - Edit products
   - `inventory.delete` - Delete products

6. **Categories**
   - `categories.view` - View categories
   - `categories.create` - Create categories
   - `categories.edit` - Edit categories
   - `categories.delete` - Delete categories

7. **Sales**
   - `sales.view` - View sales
   - `sales.create` - Process sales
   - `sales.delete` - Delete sales

8. **Reports**
   - `reports.view` - View all reports

9. **Settings**
   - `settings.view` - View settings
   - `settings.edit` - Edit settings

10. **Users**
    - `users.view` - View users
    - `users.create` - Create users
    - `users.edit` - Edit users
    - `users.delete` - Delete users

11. **Roles**
    - `roles.view` - View roles
    - `roles.create` - Create roles
    - `roles.edit` - Edit roles
    - `roles.delete` - Delete roles

### Wildcard Permission
- `*` - Grant all permissions (typically for admins)

## Default Roles

### Administrator
- **Permissions**: `*` (all permissions)
- **Description**: Full system access with all permissions
- **System Role**: Yes (cannot be deleted)

### Manager
- **Permissions**: 
  - Dashboard, Purchases (all), Suppliers (all), Inventory (all), Categories (all)
  - Sales (view only), Reports (view), Settings (view only)
- **Description**: Manage inventory, purchases, and view reports
- **System Role**: Yes (cannot be deleted)

### Cashier
- **Permissions**: 
  - Dashboard (view), POS (view), Inventory (view), Categories (view)
  - Sales (view, create)
- **Description**: Process sales and view products
- **System Role**: Yes (cannot be deleted)

## Usage

### Creating a New Role

1. Navigate to **Settings > Roles & Permissions**
2. Click **Create New Role** button
3. Fill in:
   - **Role Name** (slug): lowercase_with_underscores (e.g., `store_manager`)
   - **Display Name**: Human-readable name (e.g., `Store Manager`)
   - **Description**: Brief description of the role
   - **Permissions**: Select the permissions for this role
4. Click **Create Role**

### Editing a Role

1. Navigate to **Settings > Roles & Permissions**
2. Click **Edit** on the role card (only available for non-system roles)
3. Update the role details
4. Click **Update Role**

### Managing Permissions

1. Navigate to **Settings > Roles & Permissions**
2. Click **Manage Permissions** on any role card
3. Select/deselect permissions by module
4. Use **Select All** to quickly toggle all permissions in a module
5. Click **Save Permissions**

### Deleting a Role

1. Navigate to **Settings > Roles & Permissions**
2. Click **Delete** on the role card
3. Confirm deletion

**Note**: 
- System roles cannot be deleted
- Roles with assigned users cannot be deleted (reassign users first)

## In Code

### Checking Permissions in Controllers

```php
// In your controller method
if (!auth()->user()->hasPermission('products.create')) {
    abort(403, 'Unauthorized');
}
```

### Protecting Routes

```php
// Single permission
Route::middleware(['auth', 'can:products.view'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
});

// Multiple permissions for different actions
Route::prefix('products')->middleware(['auth'])->group(function () {
    Route::middleware(['can:products.view'])->group(function () {
        Route::get('/', [ProductController::class, 'index']);
    });
    
    Route::middleware(['can:products.create'])->group(function () {
        Route::post('/', [ProductController::class, 'store']);
    });
});
```

### Checking Permissions in Blade Views

```blade
@canPermission('products.create')
    <button>Create Product</button>
@endcanPermission

@if(auth()->user()->hasPermission('products.edit'))
    <a href="#">Edit</a>
@endif
```

### Checking Multiple Permissions

```php
// Check if user has ANY of the permissions
$user->role->hasAnyPermission(['products.view', 'products.edit']);

// Check if user has ALL of the permissions
$user->role->hasAllPermissions(['products.view', 'products.edit']);
```

## Adding New Permissions

1. **Update config/permissions.php**
   ```php
   'new_module' => [
       'label' => 'New Module',
       'description' => 'Module description',
       'permissions' => [
           'view' => 'View items',
           'create' => 'Create items',
       ],
   ],
   ```

2. **Protect routes** in `routes/web.php`
   ```php
   Route::middleware(['can:new_module.view'])->group(function () {
       // Your routes
   });
   ```

3. **Update sidebar** in `resources/views/components/layout/sidebar.blade.php`
   ```php
   [
       'name' => 'New Module',
       'route' => 'newmodule.index',
       'icon' => 'icon-name',
       'type' => 'single',
       'permission' => 'new_module.view',
   ],
   ```

## Files Structure

```
app/
├── Helpers/
│   └── PermissionHelper.php          # Helper functions for permissions
├── Http/
│   ├── Controllers/
│   │   └── SettingsController.php    # Role CRUD operations
│   └── Middleware/
│       ├── CheckPermission.php        # Permission middleware
│       └── CheckRole.php              # Role middleware
├── Models/
│   ├── User.php                       # User model with permission methods
│   └── UserRole.php                   # Role model with permission logic
└── Providers/
    └── AppServiceProvider.php         # Blade directives and middleware aliases

config/
└── permissions.php                    # Centralized permission definitions

database/
├── migrations/
│   └── 2025_11_27_184436_create_user_roles_table.php
└── seeders/
    └── UpdateRolePermissionsSeeder.php

resources/views/
├── components/layout/
│   └── sidebar.blade.php              # Permission-filtered navigation
└── settings/
    └── roles.blade.php                # Role management UI

routes/
└── web.php                            # Protected routes
```

## Troubleshooting

### User can't access a route
1. Check if the user's role has the required permission
2. Verify the permission exists in `config/permissions.php`
3. Check if the route is protected with correct middleware

### Sidebar menu item not showing
1. Verify the permission is set correctly in the navigation array
2. Check if the user has the required permission
3. For menu groups, ensure at least one child item is accessible

### Cannot create/edit roles
1. Ensure you have `roles.create` or `roles.edit` permission
2. Check that role name follows the slug format (lowercase_with_underscores)
3. Verify permissions array is valid

## Security Notes

- Never grant `*` permission to non-admin roles
- Regularly review role permissions
- System roles should only be modified by administrators
- Always protect sensitive routes with appropriate permissions
- Test permission changes with non-admin users before deployment
