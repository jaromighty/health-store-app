<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $store_id
 * @property string $zip_code
 * @property string $fulfillment_type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['store_id', 'zip_code', 'fulfillment_type', 'is_active'])]
class ServiceArea extends Model
{
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
