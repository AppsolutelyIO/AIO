<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\TaxRateType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'country',
        'region',
        'type',
        'rate',
        'priority',
        'is_compound',
        'is_active',
    ];

    protected $casts = [
        'type'        => TaxRateType::class,
        'rate'        => 'integer',
        'priority'    => 'integer',
        'is_compound' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function calculateTax(int $amount): int
    {
        return match ($this->type) {
            TaxRateType::Percentage => (int) floor($amount * $this->rate / BASIS_POINTS_DIVISOR),
            TaxRateType::Fixed      => $this->rate,
        };
    }
}
