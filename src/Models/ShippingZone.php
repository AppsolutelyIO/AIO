<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'countries',
        'regions',
        'sort',
        'is_active',
    ];

    protected $casts = [
        'countries' => 'array',
        'regions'   => 'array',
        'sort'      => 'integer',
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function activeRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class)->where('is_active', true);
    }
}
