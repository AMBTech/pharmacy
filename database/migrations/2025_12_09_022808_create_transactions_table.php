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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type'); // sale | refund | expense | payment
            $table->unsignedBigInteger('related_id')->nullable(); // sale_id, return_order_id, etc.
            $table->string('related_type')->nullable(); // 'sale', 'return_order', etc (polymorphic optional)
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // cash, card, bank
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // who created this transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
