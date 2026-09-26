<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property int $slot_booking_id
 * @property string $type
 * @property string $status
 * @property string|null $delivery_address
 * @property string|null $delivery_city
 * @property string|null $delivery_state
 * @property string|null $delivery_zip
 * @property string|null $vehicle_description
 * @property float $fee
 * @property float $tip
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property SlotBooking $slotBooking
 */
#[Fillable(['user_id', 'store_id', 'slot_booking_id', 'type', 'status', 'delivery_address', 'delivery_city',
    'delivery_state', 'delivery_zip', 'vehicle_description', 'fee', 'tip'])]
class Fulfillment extends Model
{
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function slotBooking(): BelongsTo
    {
        return $this->belongsTo(SlotBooking::class);
    }

    public function getScheduledWindowAttribute(): ?string
    {
        $slot = $this->slotBooking?->timeSlot;
        if (! $slot) {
            return null;
        }

        return $this->slotBooking->slot_date->format('D, M j').
            ' · '.$slot->starts_at->format('g:i A').' – '.$slot->ends_at->format('g:i A');
    }
}
