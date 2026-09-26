<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $store_id
 * @property int $product_id
 * @property int $quantity_in_stock
 * @property int $reserved_quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['store_id', 'product_id', 'quantity_in_stock', 'reserved_quantity'])]
class StoreInventory extends Model
{
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get available quantity (Stock - Reserved)
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity_in_stock - $this->reserved_quantity);
    }
}
