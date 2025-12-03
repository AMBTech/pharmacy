<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Pain & Fever
            [
                'name' => 'Paracetamol 500mg',
                'generic_name' => 'Acetaminophen',
                'brand' => 'Panadol',
                'category' => 'Pain Relief',
                'price' => 50.00,
                'stock' => 150,
                'unit' => 'Tablet',
                'description' => 'For fever and mild to moderate pain relief',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'BATCH001',
                        'manufacturing_date' => '2024-01-15',
                        'expiry_date' => '2026-01-15',
                        'quantity' => 100,
                        'cost_price' => 35.00,
                        'selling_price' => 50.00,
                    ],
                    [
                        'batch_number' => 'BATCH002',
                        'manufacturing_date' => '2024-02-01',
                        'expiry_date' => '2026-02-01',
                        'quantity' => 50,
                        'cost_price' => 36.00,
                        'selling_price' => 50.00,
                    ]
                ]
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'generic_name' => 'Ibuprofen',
                'brand' => 'Brufen',
                'category' => 'Pain Relief',
                'price' => 85.00,
                'stock' => 80,
                'unit' => 'Tablet',
                'description' => 'Non-steroidal anti-inflammatory drug for pain and inflammation',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'IBU001',
                        'manufacturing_date' => '2024-01-20',
                        'expiry_date' => '2026-01-20',
                        'quantity' => 80,
                        'cost_price' => 60.00,
                        'selling_price' => 85.00,
                    ]
                ]
            ],

            // Antibiotics
            [
                'name' => 'Amoxicillin 500mg',
                'generic_name' => 'Amoxicillin Trihydrate',
                'brand' => 'Moxlin',
                'category' => 'Antibiotic',
                'price' => 120.00,
                'stock' => 60,
                'unit' => 'Capsule',
                'description' => 'Broad-spectrum penicillin antibiotic',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'AMOX001',
                        'manufacturing_date' => '2024-01-10',
                        'expiry_date' => '2025-07-10',
                        'quantity' => 60,
                        'cost_price' => 85.00,
                        'selling_price' => 120.00,
                    ]
                ]
            ],
            [
                'name' => 'Azithromycin 250mg',
                'generic_name' => 'Azithromycin',
                'brand' => 'Zithromax',
                'category' => 'Antibiotic',
                'price' => 200.00,
                'stock' => 45,
                'unit' => 'Tablet',
                'description' => 'Macrolide antibiotic for bacterial infections',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'AZI001',
                        'manufacturing_date' => '2024-02-01',
                        'expiry_date' => '2026-02-01',
                        'quantity' => 45,
                        'cost_price' => 150.00,
                        'selling_price' => 200.00,
                    ]
                ]
            ],

            // Cold & Cough
            [
                'name' => 'Cetirizine 10mg',
                'generic_name' => 'Cetirizine Hydrochloride',
                'brand' => 'Zyrtec',
                'category' => 'Allergy',
                'price' => 65.00,
                'stock' => 120,
                'unit' => 'Tablet',
                'description' => 'Antihistamine for allergy relief',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'CET001',
                        'manufacturing_date' => '2024-01-15',
                        'expiry_date' => '2026-01-15',
                        'quantity' => 120,
                        'cost_price' => 45.00,
                        'selling_price' => 65.00,
                    ]
                ]
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'generic_name' => 'Ascorbic Acid',
                'brand' => 'Redoxon',
                'category' => 'Vitamin',
                'price' => 95.00,
                'stock' => 200,
                'unit' => 'Tablet',
                'description' => 'Immune support and antioxidant',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'VITC001',
                        'manufacturing_date' => '2024-01-01',
                        'expiry_date' => '2026-01-01',
                        'quantity' => 200,
                        'cost_price' => 65.00,
                        'selling_price' => 95.00,
                    ]
                ]
            ],

            // Gastrointestinal
            [
                'name' => 'Omeprazole 20mg',
                'generic_name' => 'Omeprazole',
                'brand' => 'Losec',
                'category' => 'Gastrointestinal',
                'price' => 110.00,
                'stock' => 75,
                'unit' => 'Capsule',
                'description' => 'Proton pump inhibitor for acid reflux',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'OME001',
                        'manufacturing_date' => '2024-01-20',
                        'expiry_date' => '2026-01-20',
                        'quantity' => 75,
                        'cost_price' => 80.00,
                        'selling_price' => 110.00,
                    ]
                ]
            ],
            [
                'name' => 'Metformin 500mg',
                'generic_name' => 'Metformin Hydrochloride',
                'brand' => 'Glucophage',
                'category' => 'Diabetes',
                'price' => 75.00,
                'stock' => 90,
                'unit' => 'Tablet',
                'description' => 'Oral anti-diabetic medication',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'MET001',
                        'manufacturing_date' => '2024-02-01',
                        'expiry_date' => '2026-02-01',
                        'quantity' => 90,
                        'cost_price' => 50.00,
                        'selling_price' => 75.00,
                    ]
                ]
            ],

            // Low Stock Items (for testing alerts)
            [
                'name' => 'Atorvastatin 20mg',
                'generic_name' => 'Atorvastatin Calcium',
                'brand' => 'Lipitor',
                'category' => 'Cholesterol',
                'price' => 150.00,
                'stock' => 8,
                'unit' => 'Tablet',
                'description' => 'Statin for cholesterol management',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'ATO001',
                        'manufacturing_date' => '2024-01-15',
                        'expiry_date' => '2026-01-15',
                        'quantity' => 8,
                        'cost_price' => 110.00,
                        'selling_price' => 150.00,
                    ]
                ]
            ],
            [
                'name' => 'Amlodipine 5mg',
                'generic_name' => 'Amlodipine Besylate',
                'brand' => 'Norvasc',
                'category' => 'Blood Pressure',
                'price' => 88.00,
                'stock' => 5,
                'unit' => 'Tablet',
                'description' => 'Calcium channel blocker for hypertension',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'AML001',
                        'manufacturing_date' => '2024-01-10',
                        'expiry_date' => '2026-01-10',
                        'quantity' => 5,
                        'cost_price' => 60.00,
                        'selling_price' => 88.00,
                    ]
                ]
            ],

            // Expiring Soon (for testing expiry alerts)
            [
                'name' => 'Aspirin 75mg',
                'generic_name' => 'Acetylsalicylic Acid',
                'brand' => 'Ecotrin',
                'category' => 'Cardiovascular',
                'price' => 45.00,
                'stock' => 60,
                'unit' => 'Tablet',
                'description' => 'Blood thinner and pain reliever',
                'image' => null,
                'is_active' => true,
                'batches' => [
                    [
                        'batch_number' => 'ASP001',
                        'manufacturing_date' => '2023-06-01',
                        'expiry_date' => '2024-06-01', // Expiring soon
                        'quantity' => 60,
                        'cost_price' => 30.00,
                        'selling_price' => 45.00,
                    ]
                ]
            ]
        ];

        foreach ($products as $productData) {
            $batches = $productData['batches'];
            unset($productData['batches']);

            $product = Product::create($productData);

            foreach ($batches as $batchData) {
                $product->batches()->create($batchData);
            }
        }
    }
}
