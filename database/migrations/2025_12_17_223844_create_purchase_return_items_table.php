<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items')->onDelete('restrict');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_cost', 12, 2);
            $table->enum('reason_type', ['damaged', 'defective', 'expired', 'wrong_item', 'excess_quantity', 'other'])->default('damaged');
            $table->text('reason')->nullable();
            $table->timestamps();

            // Use custom names for indexes to avoid length issues
            $table->index(['purchase_return_id', 'purchase_order_item_id'], 'pr_items_pr_id_poi_id_idx');
            $table->index('purchase_order_item_id', 'pr_items_poi_id_idx');

            // Optional: Add unique constraint to prevent duplicate returns of same item
            $table->unique(['purchase_return_id', 'purchase_order_item_id'], 'pr_items_pr_id_poi_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};
