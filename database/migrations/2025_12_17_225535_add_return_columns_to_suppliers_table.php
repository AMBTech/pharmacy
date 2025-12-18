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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->integer('total_returns')->default(0)->after('notes');
            $table->decimal('total_return_amount', 12, 2)->default(0)->after('total_returns');
            $table->decimal('return_rate', 5, 2)->virtualAs('IF(total_purchases > 0, (total_return_amount / total_purchases * 100), 0)')->after('total_return_amount');

            if (!Schema::hasColumn('suppliers', 'total_purchases')) {
                $table->decimal('total_purchases', 12, 2)->default(0)->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['total_returns', 'total_return_amount', 'return_rate']);

            if (Schema::hasColumn('suppliers', 'total_purchases')) {
                $table->dropColumn('total_purchases');
            }
        });
    }
};
