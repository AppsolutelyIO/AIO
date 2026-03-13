<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use App\Models\User;

use Appsolutely\AIO\Enums\ReviewStatus;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use HasFactory;
    use ScopeStatus;
    use SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'body',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'status'      => ReviewStatus::class,
        'verified_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
