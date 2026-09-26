<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $description
 * @property float $price
 * @property float $unit_price
 * @property string $unit_type
 * @property string $image_url
 * @property array $certifications
 * @property string $short_description
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category_id', 'name', 'slug', 'sku', 'description', 'price', 'unit_price', 'unit_type', 'image_url', 'certifications', 'short_description', 'is_active'])]
class Product extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'certifications' => 'array',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope for filtering certifications
     */
    public function scopeHasCertification($query, string $key)
    {
        return $query->whereJsonContains('certifications', $key);
    }

    /**
     * Scope for active products only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
