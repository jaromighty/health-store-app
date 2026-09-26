<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $produce = Category::firstOrCreate(['slug' => 'produce'], ['name' => 'Fresh Produce']);
        $vitamins = Category::firstOrCreate(['slug' => 'vitamins'], ['name' => 'Vitamins & Supplements']);
        $pantry = Category::firstOrCreate(['slug' => 'pantry'], ['name' => 'Pantry']);

        // Create Products
        $products = [
            [
                'name' => 'Organic Bananas',
                'slug' => 'organic-bananas',
                'sku' => 'NG-BAN-001',
                'category_id' => $produce->id,
                'price' => 0.69,
                'unit_type' => 'lb',
                'certifications' => ['organic', 'non_gmo'],
                'short_description' => 'Sweet, yellow bananas grown without synthetic pesticides.',
            ],
            [
                'name' => 'Non-GMO Almond Butter',
                'slug' => 'non-gmo-almond-butter',
                'sku' => 'NG-ALM-002',
                'category_id' => $pantry->id,
                'price' => 12.99,
                'unit_type' => 'jar',
                'certifications' => ['non_gmo', 'gluten_free', 'vegan'],
                'short_description' => 'Creamy almond butter with no added oils or sugar.',
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'slug' => 'vitamin-c-1000mg',
                'sku' => 'NG-VIT-003',
                'category_id' => $vitamins->id,
                'price' => 18.50,
                'unit_type' => 'bottle',
                'certifications' => ['non_gmo', 'gluten_free'],
                'short_description' => 'High potency Vitamin C for immune support.',
            ],
            [
                'name' => 'Regular Apples',
                'slug' => 'regular-apples',
                'sku' => 'NG-APP-004',
                'category_id' => $produce->id,
                'price' => 1.29,
                'unit_type' => 'lb',
                'certifications' => [], // Not organic
                'short_description' => 'Crisp red apples, conventionally grown.',
            ],
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
