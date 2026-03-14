<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::userModel());
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }
}
