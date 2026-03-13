<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Form extends Model
{
    use HasFactory;
    use ScopeStatus;

    protected $fillable = [
        'api_access_token',
        'meta_keys_to_collect',
        'name',
        'slug',
        'description',
        'target_table',
        'status',
    ];

    protected $casts = [
        'meta_keys_to_collect' => 'array',
        'status'               => Status::class,
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FormEntry::class);
    }

    /**
     * Get active form fields
     */
    public function activeFields(): HasMany
    {
        return $this->fields();
    }

    /**
     * Get non-spam entries
     */
    public function validEntries(): HasMany
    {
        return $this->entries()->where('is_spam', FormEntrySpamStatus::Valid);
    }

    /**
     * Get entries count
     */
    public function getEntriesCountAttribute(): int
    {
        return $this->entries()->count();
    }

    /**
     * Get valid entries count
     */
    public function getValidEntriesCountAttribute(): int
    {
        return $this->validEntries()->count();
    }
}
