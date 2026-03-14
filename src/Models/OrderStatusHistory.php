<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'user_id',
        'note',
    ];

    protected $casts = [
        'from_status' => OrderStatus::class,
        'to_status'   => OrderStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(self::userModel());
    }
}
