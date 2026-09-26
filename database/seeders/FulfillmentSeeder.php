<?php

namespace Database\Seeders;

use App\Models\ServiceArea;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class FulfillmentSeeder extends Seeder
{
    public function run(): void
    {
        // Assume stores 1 & 2 exist from StoreInventorySeeder
        foreach ([1, 2] as $storeId) {
            // Pickup slots: daily, generous capacity (small store => moderate capacity)
            foreach (['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'] as $day) {
                TimeSlot::updateOrCreate(
                    ['store_id' => $storeId, 'fulfillment_type' => 'pickup', 'day_of_week' => $day, 'starts_at' => '09:00', 'ends_at' => '11:00'],
                    ['capacity' => 8, 'is_active' => true]
                );
                TimeSlot::updateOrCreate(
                    ['store_id' => $storeId, 'fulfillment_type' => 'pickup', 'day_of_week' => $day, 'starts_at' => '15:00', 'ends_at' => '17:00'],
                    ['capacity' => 8, 'is_active' => true]
                );

                // Delivery slots (skip Sundays for simplicity)
                if ($day !== 'sun') {
                    TimeSlot::updateOrCreate(
                        ['store_id' => $storeId, 'fulfillment_type' => 'delivery', 'day_of_week' => $day, 'starts_at' => '10:00', 'ends_at' => '12:00'],
                        ['capacity' => 6, 'is_active' => true]
                    );
                    TimeSlot::updateOrCreate(
                        ['store_id' => $storeId, 'fulfillment_type' => 'delivery', 'day_of_week' => $day, 'starts_at' => '16:00', 'ends_at' => '18:00'],
                        ['capacity' => 6, 'is_active' => true]
                    );
                }
            }

            // Delivery service areas (Boulder & Aurora zips)
            $zips = $storeId === 1
                ? ['80301', '80302', '80303', '80304']
                : ['80012', '80014', '80017'];
            foreach ($zips as $zip) {
                ServiceArea::updateOrCreate(
                    ['store_id' => $storeId, 'zip_code' => $zip, 'fulfillment_type' => 'delivery'],
                    ['is_active' => true]
                );
            }
        }
    }
}
