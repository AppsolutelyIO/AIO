<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\DeliveryTokenStatus;
use Appsolutely\AIO\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'token',
        'product_type',
        'status',
        'delivery_channel',
        'delivery_payload',
        'delivery_response',
        'delivered_by',
        'expires_at',
        'delivered_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'product_type'      => ProductType::class,
        'status'            => DeliveryTokenStatus::class,
        'delivery_response' => 'array',
        'expires_at'        => 'datetime',
        'delivered_at'      => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === DeliveryTokenStatus::Pending;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isDeliverable(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }
}
