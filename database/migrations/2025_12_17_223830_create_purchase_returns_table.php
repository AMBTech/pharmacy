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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('restrict');
            $table->date('return_date');
            $table->enum('return_type', ['full_refund', 'partial_refund', 'replacement', 'store_credit'])->default('full_refund');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('reason')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('restocking_fee', 5, 2)->default(0); // percentage
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['return_number', 'status']);
            $table->index('purchase_order_id');
            $table->index(['return_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {

            // Drop foreign key constraints
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);

            // Drop indexes
            $table->dropIndex(['return_number', 'status']);
            $table->dropIndex(['purchase_order_id']);
            $table->dropIndex(['return_date', 'status']);
        });

        Schema::dropIfExists('purchase_returns');
    }

};
