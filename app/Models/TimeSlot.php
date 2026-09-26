<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $store_id
 * @property string $fulfillment_type
 * @property string $day_of_week
 * @property string $starts_at
 * @property string $ends_at
 * @property int $capacity
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['store_id', 'fulfillment_type', 'day_of_week', 'starts_at', 'ends_at', 'capacity', 'is_active'])]
class TimeSlot extends Model
{
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime:H:i',
            'ends_at' => 'datetime:H:i',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(SlotBooking::class);
    }

    public function bookingForDate(string $date): ?SlotBooking
    {
        return $this->bookings()->where('slot_date', $date)->first();
    }

    public function remainingCapacityForDate(string $date): int
    {
        $booking = $this->bookingForDate($date);

        return max(0, $this->capacity - ($booking?->booked_count ?? 0));
    }
}
