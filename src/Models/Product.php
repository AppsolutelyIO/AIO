<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\HasFilesOfType;
use Appsolutely\AIO\Models\Concerns\HasMarkdownContent;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Appsolutely\AIO\Models\Concerns\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use HasFilesOfType;
    use HasMarkdownContent;
    use ScopePublished;
    use ScopeStatus;
    use Sluggable;
    use SoftDeletes;

    const array SHIPMENT_METHOD_PHYSICAL_PRODUCT = ['App\Models\UserAddress'];

    const array SHIPMENT_METHOD_AUTO_DELIVERABLE_VIRTUAL_PRODUCT = ['App\Models\User'];

    const array SHIPMENT_METHOD_MANUAL_DELIVERABLE_VIRTUAL_PRODUCT = [
        'Email',
        'Mobile',
        'Whatsapp',
        'Telegram',
        'WeChat',
        'Weixin',
    ];

    protected $fillable = [
        'type',
        'shipment_methods',
        'slug',
        'title',
        'subtitle',
        'cover',
        'keywords',
        'description',
        'content',
        'original_price',
        'price',
        'setting',
        'payment_methods',
        'additional_columns',
        'sort',
        'status',
        'published_at',
        'expired_at',
    ];

    protected $casts = [
        'type'               => ProductType::class,
        'shipment_methods'   => 'array',
        'setting'            => 'array',
        'payment_methods'    => 'array',
        'additional_columns' => 'array',
        'published_at'       => 'datetime',
        'expired_at'         => 'datetime',
        'status'             => Status::class,
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_pivot');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(ProductSku::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
