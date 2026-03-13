<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class NotificationSender extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'service_config',
        'from_address',
        'from_name',
        'category',
        'is_default',
        'priority',
        'is_active',
        'daily_limit',
        'hourly_limit',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'smtp_port'    => 'integer',
        'is_default'   => 'boolean',
        'is_active'    => 'boolean',
        'priority'     => 'integer',
        'daily_limit'  => 'integer',
        'hourly_limit' => 'integer',
    ];

    protected $hidden = ['smtp_password', 'service_config'];

    /**
     * Get rules using this sender
     */
    public function rules(): HasMany
    {
        return $this->hasMany(NotificationRule::class, 'sender_id');
    }

    /**
     * Get queue items using this sender
     */
    public function queueItems(): HasMany
    {
        return $this->hasMany(NotificationQueue::class, 'sender_id');
    }

    /**
     * Scope: Active senders only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Default senders
     */
    public function scopeDefault(Builder $query, ?string $category = null): Builder
    {
        $query = $query->where('is_default', true);
        if ($category) {
            $query->where('category', $category);
        }

        return $query;
    }

    /**
     * Get decrypted password
     */
    public function getDecryptedPasswordAttribute(): ?string
    {
        return $this->smtp_password ? decrypt($this->smtp_password) : null;
    }

    /**
     * Get decrypted service config
     */
    public function getDecryptedServiceConfigAttribute(): ?array
    {
        if (! $this->service_config) {
            return null;
        }

        return json_decode(decrypt($this->service_config), true);
    }

    /**
     * Set password with encryption
     */
    public function setSmtpPasswordAttribute(?string $value): void
    {
        $this->attributes['smtp_password'] = $value ? encrypt($value) : null;
    }

    /**
     * Set service config with encryption
     */
    public function setServiceConfigAttribute(?array $value): void
    {
        $this->attributes['service_config'] = $value ? encrypt(json_encode($value)) : null;
    }

    /**
     * Check if sender is internal
     */
    public function isInternal(): bool
    {
        return $this->category === 'internal';
    }

    /**
     * Check if sender is external
     */
    public function isExternal(): bool
    {
        return $this->category === 'external';
    }

    /**
     * Check if sender is system
     */
    public function isSystem(): bool
    {
        return $this->category === 'system';
    }

    /**
     * Get usage statistics
     */
    public function getUsageStatsAttribute(): array
    {
        $queueStats = $this->queueItems()
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent_count', [NotificationQueueStatus::Sent->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_count', [NotificationQueueStatus::Pending->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed_count', [NotificationQueueStatus::Failed->value])
            ->first();

        return [
            'rules_count'   => $this->rules()->count(),
            'sent_count'    => (int) $queueStats->sent_count,
            'pending_count' => (int) $queueStats->pending_count,
            'failed_count'  => (int) $queueStats->failed_count,
        ];
    }
}
