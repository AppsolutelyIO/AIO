<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use App\Models\User;

use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Models\Concerns\ScopeReference;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasFactory;
    use ScopeReference;
    use ScopeStatus;
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'order_id',
        'order_payment_id',
        'user_id',
        'amount',
        'status',
        'reason',
        'admin_note',
        'vendor_reference',
        'vendor_extra_info',
        'refunded_at',
    ];

    protected $casts = [
        'status'            => RefundStatus::class,
        'vendor_extra_info' => 'array',
        'refunded_at'       => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderPayment(): BelongsTo
    {
        return $this->belongsTo(OrderPayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
