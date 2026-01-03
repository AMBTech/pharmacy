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
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();

            $table->string('bucket_code', 20);   // e.g. B01
            $table->string('shelf_code', 20);    // e.g. S2
            $table->string('slot_code', 20)->nullable(); // e.g. P4

            $table->string('label')->nullable(); // "Hearing Aids – Small"
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate physical locations
            $table->unique(['bucket_code', 'shelf_code', 'slot_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};
