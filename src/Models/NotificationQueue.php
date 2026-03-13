<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NotificationQueue extends Model
{
    use HasFactory;

    protected $table = 'notification_queue';

    protected $fillable = [
        'rule_id',
        'template_id',
        'sender_id',
        'form_entry_id',
        'recipient_email',
        'subject',
        'body_html',
        'body_text',
        'trigger_data',
        'status',
        'scheduled_at',
        'sent_at',
        'error_message',
        'attempts',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
        'attempts'     => 'integer',
        'status'       => NotificationQueueStatus::class,
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(NotificationRule::class, 'rule_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(NotificationSender::class, 'sender_id');
    }

    public function formEntry(): BelongsTo
    {
        return $this->belongsTo(FormEntry::class, 'form_entry_id');
    }

    /**
     * Scope for pending notifications
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', NotificationQueueStatus::Pending);
    }

    /**
     * Scope for sent notifications
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', NotificationQueueStatus::Sent);
    }

    /**
     * Scope for failed notifications
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', NotificationQueueStatus::Failed);
    }

    /**
     * Scope for ready to send notifications
     */
    public function scopeReadyToSend(Builder $query): Builder
    {
        return $query->where('status', NotificationQueueStatus::Pending)
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Check if notification is ready to send
     */
    public function getIsReadyToSendAttribute(): bool
    {
        return $this->status === NotificationQueueStatus::Pending && $this->scheduled_at <= now();
    }

    /**
     * Mark as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status'  => NotificationQueueStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status'        => NotificationQueueStatus::Failed,
            'error_message' => $errorMessage,
            'attempts'      => $this->attempts + 1,
        ]);
    }

    /**
     * Retry failed notification
     */
    public function retry(): void
    {
        $this->update([
            'status'        => NotificationQueueStatus::Pending,
            'scheduled_at'  => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Get formatted trigger data
     */
    public function getFormattedTriggerDataAttribute(): string
    {
        if (empty($this->trigger_data)) {
            return 'No data';
        }

        return collect($this->trigger_data)
            ->map(fn ($value, $key) => "{$key}: " . (is_array($value) ? json_encode($value) : (string) ($value ?? '')))
            ->implode(', ');
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return NotificationQueueStatus::values();
    }

    /**
     * Get status labels
     */
    public static function getStatusLabels(): array
    {
        return NotificationQueueStatus::toArray();
    }

    /**
     * Get status colors (Bootstrap classes)
     */
    public static function getStatusColors(): array
    {
        return NotificationQueueStatus::toColorArray();
    }

    /**
     * Get status label for current notification
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Get status color for current notification
     */
    public function getStatusColorAttribute(): string
    {
        return $this->status->color();
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        return "<span class='badge bg-{$this->status_color}'>{$this->status_label}</span>";
    }
}
