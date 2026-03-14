<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Concerns\ScopeReference;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use ScopeReference;
    use ScopeStatus;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'reference',
        'summary',
        'amount',
        'discounted_amount',
        'total_amount',
        'status',
        'delivery_info',
        'note',
        'remark',
        'ip',
        'request',
    ];

    protected $casts = [
        'status'        => OrderStatus::class,
        'delivery_info' => 'array',
        'request'       => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::userModel());
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function couponUsage(): HasOne
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }

    public function deliveryTokens(): HasMany
    {
        return $this->hasMany(DeliveryToken::class);
    }
}
