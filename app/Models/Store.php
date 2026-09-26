<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $zip_code
 * @property string $latitude
 * @property string $longitude
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'address', 'city', 'state', 'zip_code', 'latitude', 'longitude', 'is_active'])]
class Store extends Model
{
    public function inventory(): HasMany
    {
        return $this->hasMany(StoreInventory::class);
    }
}
