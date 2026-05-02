<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\HasFilesOfType;
use Appsolutely\AIO\Models\Concerns\HasMarkdownContent;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Appsolutely\AIO\Models\Concerns\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory;
    use HasFilesOfType;
    use HasMarkdownContent;
    use ScopePublished;
    use ScopeStatus;
    use Sluggable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'keywords',
        'description',
        'content',
        'slug',
        'cover',
        'status',
        'published_at',
        'expired_at',
        'sort',
    ];

    protected $casts = [
        'setting'      => 'array',
        'published_at' => 'datetime',
        'expired_at'   => 'datetime',
        'status'       => Status::class,
    ];

    public static function defaultSetting(): array
    {
        return ['meta_title' => ''];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ArticleCategory::class, 'article_category_pivot');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag_pivot');
    }
}
