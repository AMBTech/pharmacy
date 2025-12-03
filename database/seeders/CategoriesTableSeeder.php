<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Antibiotics', 'color' => '#ef4444', 'sort_order' => 1],
            ['name' => 'Pain Relief', 'color' => '#3b82f6', 'sort_order' => 2],
            ['name' => 'Vitamins', 'color' => '#10b981', 'sort_order' => 3],
            ['name' => 'Allergy', 'color' => '#f59e0b', 'sort_order' => 4],
            ['name' => 'Diabetes', 'color' => '#8b5cf6', 'sort_order' => 5],
            ['name' => 'Cardiovascular', 'color' => '#ec4899', 'sort_order' => 6],
            ['name' => 'Gastrointestinal', 'color' => '#06b6d4', 'sort_order' => 7],
            ['name' => 'Dermatological', 'color' => '#84cc16', 'sort_order' => 8],
            ['name' => 'First Aid', 'color' => '#f97316', 'sort_order' => 9],
            ['name' => 'Other', 'color' => '#6b7280', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
