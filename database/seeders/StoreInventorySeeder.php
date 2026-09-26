<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreInventory;
use Illuminate\Database\Seeder;

class StoreInventorySeeder extends Seeder
{
    public function run(): void
    {
        // Create Stores
        $store1 = Store::firstOrCreate(
            ['zip_code' => '80302'],
            ['name' => 'Natural Grocers Boulder', 'city' => 'Boulder', 'state' => 'CO', 'address' => '123 Pearl St']
        );

        $store2 = Store::firstOrCreate(
            ['zip_code' => '80014'],
            ['name' => 'Natural Grocers Aurora', 'city' => 'Aurora', 'state' => 'CO', 'address' => '456 Main St']
        );

        // Get Products (Assuming seeder ran previously)
        $products = Product::all();

        // Populate Inventory
        foreach ($products as $product) {
            // Store 1: Good stock
            StoreInventory::updateOrCreate(
                ['store_id' => $store1->id, 'product_id' => $product->id],
                ['quantity_in_stock' => rand(10, 50), 'reserved_quantity' => 0]
            );

            // Store 2: Low stock for some items
            $stock = rand(0, 20);
            StoreInventory::updateOrCreate(
                ['store_id' => $store2->id, 'product_id' => $product->id],
                ['quantity_in_stock' => $stock, 'reserved_quantity' => 0]
            );
        }
    }
}
