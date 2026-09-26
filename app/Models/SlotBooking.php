<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $time_slot_id
 * @property Carbon $slot_date
 * @property int $booked_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property TimeSlot $timeSlot
 */
#[Fillable(['time_slot_id', 'slot_date', 'booked_count'])]
class SlotBooking extends Model
{
    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
        ];
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }
}
