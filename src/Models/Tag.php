<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Appsolutely\AIO\Models\Concerns\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory;
    use ScopeStatus;
    use Sluggable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'sort',
    ];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag_pivot');
    }
}
