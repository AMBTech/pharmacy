<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // Insert default roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access',
                'is_system' => true,
                'permissions' => json_encode(['*']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Manage products, categories, and view reports',
                'is_system' => true,
                'permissions' => json_encode([
                    'products.view', 'products.create', 'products.edit', 'products.delete',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                    'sales.view', 'reports.view', 'settings.view'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'Process sales and view products',
                'is_system' => true,
                'permissions' => json_encode([
                    'products.view', 'sales.create', 'sales.view'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('user_roles')->insert($roles);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
