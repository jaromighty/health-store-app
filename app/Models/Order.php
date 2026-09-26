<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $number
 * @property int $user_id
 * @property int $store_id
 * @property int $fulfillment_id
 * @property float $subtotal
 * @property float $fulfillment_fee
 * @property float $tip
 * @property float $tax
 * @property float $total
 * @property string $status
 * @property string|null $payment_gateway
 * @property string|null $payment_gateway_ref
 * @property Carbon|null $paid_at
 * @property Carbon|null $shopped_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['number', 'user_id', 'store_id', 'fulfillment_id', 'subtotal', 'fulfillment_fee', 'tip', 'tax', 'total',
    'status', 'payment_gateway', 'payment_gateway_ref', 'paid_at', 'shopped_at', 'completed_at'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'shopped_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** Legal state transitions — the "state machine" */
    public const array TRANSITIONS = [
        'pending_payment' => ['confirmed', 'cancelled'],
        'confirmed' => ['shopping', 'cancelled'],
        'shopping' => ['ready_for_pickup', 'out_for_delivery', 'cancelled'],
        'ready_for_pickup' => ['completed', 'cancelled'],
        'out_for_delivery' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number ??= 'NG-'.now()->format('Y').'-'.
                str_pad((string) (static::max('id') + 1), 6, '0', STR_PAD_LEFT);
        });
    }

    public function transitionTo(string $status): void
    {
        if (! in_array($status, static::TRANSITIONS[$this->status] ?? [], true)) {
            throw new \InvalidArgumentException(
                "Cannot transition order {$this->number} from '{$this->status}' to '{$status}'."
            );
        }

        $this->update(['status' => $status]);

        if ($status === 'completed') {
            $this->update(['completed_at' => now()]);
        }
        if ($status === 'cancelled' && $this->paid_at) {
            $this->refund(); // notify, at minimum
        }
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(Fulfillment::class);
    }

    public function refund(): void
    {
        // Fire an event / queue job here; gateway refunds should never be synchronous in prod
    }
}
