<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddBarcodesToProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products without barcodes
        $products = Product::whereNull('barcode')->get();

        foreach ($products as $product) {
            // Generate a random barcode (EAN-13 format: 13 digits)
            // First 3 digits: Country code (890 for India)
            // Next 4-5 digits: Manufacturer code
            // Next 4-5 digits: Product code
            // Last digit: Check digit
            $barcode = '890' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            
            $product->update([
                'barcode' => $barcode
            ]);
        }

        $this->command->info('Barcodes added to ' . $products->count() . ' products.');
    }
}
