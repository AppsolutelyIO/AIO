<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\HasFilesOfType;
use Appsolutely\AIO\Models\Concerns\HasMarkdownContent;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Appsolutely\AIO\Models\Concerns\Sluggable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ProductSku extends Model implements Sortable
{
    use HasFilesOfType;
    use HasMarkdownContent;
    use ScopePublished;
    use ScopeStatus;
    use Sluggable;
    use SoftDeletes;
    use SortableTrait;

    public array $sortable = [
        'order_column_name'  => 'sort',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'product_id',
        'attributes',
        'slug',
        'title',
        'subtitle',
        'cover',
        'keywords',
        'description',
        'content',
        'stock',
        'original_price',
        'price',
        'sort',
        'status',
        'published_at',
        'expired_at',
    ];

    protected $casts = [
        'attributes'   => 'array',
        'stock'        => 'integer',
        'sort'         => 'integer',
        'status'       => Status::class,
        'published_at' => 'datetime',
        'expired_at'   => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTitle(): string
    {
        return $this->title ?: ($this->product?->title ?? '');
    }

    public function getSubtitle(): string
    {
        return $this->subtitle ?: ($this->product?->subtitle ?? '');
    }

    public function getCover(): ?string
    {
        return $this->cover ?: $this->product?->cover;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords ?: $this->product?->keywords;
    }

    public function getDescription(): ?string
    {
        return $this->description ?: $this->product?->description;
    }

    public function getContent(): ?string
    {
        return $this->content ?: $this->product?->content;
    }

    public function getOriginalPrice(): ?string
    {
        return $this->original_price ?: $this->product?->original_price;
    }

    protected function getSlugConfig(): array
    {
        return [
            'source_field' => 'title',
            'slug_field'   => 'slug',
            'parent_field' => 'product_id',
        ];
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_sku_attribute_value');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort');
    }
}
