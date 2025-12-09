<?php

return [
    'modules' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'description' => 'Dashboard access and analytics',
            'permissions' => [
                'view' => 'View dashboard',
            ],
        ],
        'pos' => [
            'label' => 'Point of Sale',
            'description' => 'Process sales transactions',
            'permissions' => [
                'view' => 'Access POS system',
                'edit' => 'Edit POS system',
            ],
        ],
        'returns' => [
            'label' => 'Returns',
            'description' => 'Process returns transactions',
            'permissions' => [
                'create' => 'Create',
                'view' => 'View',
                'store' => 'Store',
                'edit' => 'Edit',
                'approve' => 'Approve',
                'reject' => 'Reject',
            ],
        ],
        'purchases' => [
            'label' => 'Purchases',
            'description' => 'Manage purchase orders',
            'permissions' => [
                'view' => 'View purchase orders',
                'create' => 'Create purchase orders',
                'edit' => 'Edit purchase orders',
                'delete' => 'Delete purchase orders',
            ],
        ],
        'suppliers' => [
            'label' => 'Suppliers',
            'description' => 'Manage supplier information',
            'permissions' => [
                'view' => 'View suppliers',
                'create' => 'Create suppliers',
                'edit' => 'Edit suppliers',
                'delete' => 'Delete suppliers',
            ],
        ],
        'inventory' => [
            'label' => 'Inventory',
            'description' => 'Manage product inventory',
            'permissions' => [
                'view' => 'View inventory',
                'create' => 'Add products',
                'edit' => 'Edit products',
                'delete' => 'Delete products',
            ],
        ],
        'batches' => [
            'label' => 'Batches',
            'description' => 'Manage product batches',
            'permissions' => [
                'view' => 'View batch',
                'create' => 'Add batch',
                'edit' => 'Edit batch',
                'delete' => 'Delete batch',
            ],
        ],
        'categories' => [
            'label' => 'Categories',
            'description' => 'Manage product categories',
            'permissions' => [
                'view' => 'View categories',
                'create' => 'Create categories',
                'edit' => 'Edit categories',
                'delete' => 'Delete categories',
            ],
        ],
        'sales' => [
            'label' => 'Sales',
            'description' => 'View and manage sales',
            'permissions' => [
                'view' => 'View sales',
                'create' => 'Process sales',
                'delete' => 'Delete sales',
            ],
        ],
        'reports' => [
            'label' => 'Reports',
            'description' => 'Access reports and analytics',
            'permissions' => [
                'view' => 'View all reports',
            ],
        ],
        'settings' => [
            'label' => 'Settings',
            'description' => 'System settings',
            'permissions' => [
                'view' => 'View settings',
                'edit' => 'Edit settings',
            ],
        ],
        'users' => [
            'label' => 'User Management',
            'description' => 'Manage system users',
            'permissions' => [
                'view' => 'View users',
                'create' => 'Create users',
                'edit' => 'Edit users',
                'delete' => 'Delete users',
            ],
        ],
        'roles' => [
            'label' => 'Roles & Permissions',
            'description' => 'Manage roles and permissions',
            'permissions' => [
                'view' => 'View roles',
                'create' => 'Create roles',
                'edit' => 'Edit roles',
                'delete' => 'Delete roles',
            ],
        ],
    ],

];
