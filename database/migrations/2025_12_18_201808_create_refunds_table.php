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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique()->nullable();
            $table->foreignId('purchase_return_id')->nullable()->constrained('purchase_returns')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');

            // Refund details
            $table->decimal('amount', 12, 2);
            $table->date('refund_date');
            $table->enum('method', ['bank_transfer', 'cash', 'check', 'credit_note', 'store_credit', 'other'])->default('bank_transfer');
            $table->string('reference')->nullable(); // Bank reference, check number, etc.
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');

            // Payment details
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('swift_code')->nullable();

            // Check details (if method is check)
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->date('check_due_date')->nullable();

            // Store credit details
            $table->decimal('credit_balance', 12, 2)->nullable();
            $table->date('credit_expiry_date')->nullable();

            // Additional information
            $table->text('notes')->nullable();
            $table->text('failure_reason')->nullable();

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['refund_number', 'status']);
            $table->index(['purchase_return_id', 'status']);
            $table->index(['supplier_id', 'refund_date']);
            $table->index(['method', 'status']);
            $table->index(['refund_date', 'status']);
            $table->index('reference');
            $table->index('check_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
