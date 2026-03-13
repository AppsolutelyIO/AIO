<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Concerns\ScopeReference;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory;
    use ScopeReference;
    use ScopeStatus;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_sku_id',
        'reference',
        'summary',
        'original_price',
        'price',
        'quantity',
        'discounted_amount',
        'amount',
        'product_snapshot',
        'note',
        'remark',
        'status',
    ];

    protected $casts = [
        'status'           => OrderStatus::class,
        'product_snapshot' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productSku(): BelongsTo
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function deliveryToken(): HasOne
    {
        return $this->hasOne(DeliveryToken::class);
    }
}
