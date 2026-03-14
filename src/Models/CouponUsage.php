<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'integer',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::userModel());
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
