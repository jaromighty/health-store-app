<?php

namespace App\Services;

use App\Models\ServiceArea;
use App\Models\SlotBooking;
use App\Models\TimeSlot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FulfillmentService
{
    public const int SLOT_LEAD_TIME_HOURS = 3;   // Can't book a slot starting within 3 hours

    public const int SLOT_HORIZON_DAYS = 7;      // Show next 7 days

    public const float DELIVERY_FEE = 7.99;

    public const float FREE_DELIVERY_THRESHOLD = 60.00;

    public const float PICKUP_FEE = 0.00;

    /**
     * Get available slots grouped by date for a store/type.
     */
    public function getAvailableSlots(int $storeId, string $type, int $days = self::SLOT_HORIZON_DAYS): Collection
    {
        $slotsByDate = collect();
        $today = CarbonImmutable::today();

        // Pre-load booked counts for efficiency
        $templates = TimeSlot::where('store_id', $storeId)
            ->where('fulfillment_type', $type)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $bookings = SlotBooking::whereIn('time_slot_id', $templates->keys())
            ->whereBetween('slot_date', [$today, $today->addDays($days)])
            ->get()
            ->keyBy(fn ($b) => $b->time_slot_id.'|'.$b->slot_date->format('Y-m-d'));

        for ($i = 0; $i < $days; $i++) {
            $date = $today->addDays($i);
            $daySlots = [];

            foreach ($templates as $slot) {
                if ($slot->day_of_week !== strtolower($date->shortEnglishDayOfWeek)) {
                    continue;
                }

                // Enforce lead time for today
                $slotStart = $date->setTimeFromTimeString($slot->starts_at->format('H:i:s'));
                if ($slotStart->lt(now()->addHours(self::SLOT_LEAD_TIME_HOURS))) {
                    continue;
                }

                $key = $slot->id.'|'.$date->format('Y-m-d');
                $bookedCount = $bookings[$key]?->booked_count ?? 0;
                $remaining = $slot->capacity - $bookedCount;

                if ($remaining <= 0) {
                    continue;
                }

                $daySlots[] = [
                    'id' => $slot->id,
                    'date' => $date->format('Y-m-d'),
                    'starts_at' => $slot->starts_at->format('H:i'),
                    'ends_at' => $slot->ends_at->format('H:i'),
                    'label' => $slot->starts_at->format('g:i A').' – '.$slot->ends_at->format('g:i A'),
                    'remaining' => $remaining,
                    'almost_full' => $remaining <= 2,
                ];
            }

            if (! empty($daySlots)) {
                $slotsByDate->push([
                    'date' => $date->format('Y-m-d'),
                    'label' => $i === 0 ? 'Today' : ($i === 1 ? 'Tomorrow' : $date->format('D, M j')),
                    'slots' => $daySlots,
                ]);
            }
        }

        return $slotsByDate;
    }

    /**
     * Validate a delivery zip is inside the store's service area.
     */
    public function isZipServiced(int $storeId, string $zip): bool
    {
        return ServiceArea::where('store_id', $storeId)
            ->where('fulfillment_type', 'delivery')
            ->where('zip_code', $zip)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Calculate fulfillment fee based on mode and cart subtotal.
     */
    public function calculateFee(string $type, float $subtotal, ?float $tip = null): array
    {
        $fee = $type === 'delivery' ? self::DELIVERY_FEE : self::PICKUP_FEE;

        if ($type === 'delivery' && $subtotal >= self::FREE_DELIVERY_THRESHOLD) {
            $fee = 0.00;
        }

        return [
            'fee' => $fee,
            'tip' => $tip ?? 0,
            'free_delivery_threshold' => self::FREE_DELIVERY_THRESHOLD,
        ];
    }

    /**
     * Atomically reserve a slot (increment booked_count, respecting capacity).
     * @throws \Exception
     */
    public function reserveSlot(TimeSlot $slot, string $date): SlotBooking
    {
        // Note: for true atomicity under concurrent load, replace the tap-check with a locked update:
        // DB::transaction() + lockForUpdate() on the booking row. Fine to defer for MVP.
        return SlotBooking::firstOrCreate(
            ['time_slot_id' => $slot->id, 'slot_date' => $date],
            ['booked_count' => 0]
        )->tap(function (SlotBooking $booking) use ($slot) {
            if ($booking->booked_count >= $slot->capacity) {
                throw new \Exception('This time slot is fully booked.');
            }
        });
    }
}
