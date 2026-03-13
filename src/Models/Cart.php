<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use App\Models\User;

use Appsolutely\AIO\Enums\CartStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'total_amount',
        'metadata',
        'converted_at',
    ];

    protected $casts = [
        'status'       => CartStatus::class,
        'metadata'     => 'array',
        'converted_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CartStatus::Active);
    }

    public function scopeAbandoned(Builder $query): Builder
    {
        return $query->where('status', CartStatus::Abandoned);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
