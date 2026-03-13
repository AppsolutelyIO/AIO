<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\NotificationTriggerType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NotificationRule extends Model
{
    use HasFactory;
    use ScopeStatus;

    protected $fillable = [
        'name',
        'trigger_type',
        'trigger_reference',
        'template_id',
        'sender_id',
        'recipient_type',
        'recipient_emails',
        'conditions',
        'delay_minutes',
        'status',
    ];

    protected $casts = [
        'trigger_type'     => NotificationTriggerType::class,
        'recipient_emails' => 'array',
        'conditions'       => 'array',
        'delay_minutes'    => 'integer',
        'status'           => Status::class,
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(NotificationSender::class, 'sender_id');
    }

    public function queueItems(): HasMany
    {
        return $this->hasMany(NotificationQueue::class, 'rule_id');
    }

    /**
     * Get recipient emails array
     */
    public function getRecipientEmailsListAttribute(): array
    {
        return $this->recipient_emails ?? [];
    }

    /**
     * Check if rule has conditions
     */
    public function getHasConditionsAttribute(): bool
    {
        return ! empty($this->conditions);
    }

    /**
     * Get scheduled timestamp based on delay
     */
    public function getScheduledAt(): \Carbon\Carbon
    {
        return now()->addMinutes($this->delay_minutes);
    }

    /**
     * Evaluate if conditions are met for given data
     */
    public function evaluateConditions(array $data): bool
    {
        if (! $this->has_conditions) {
            return true;
        }

        $conditions = $this->conditions;

        // Simple condition evaluation
        // Format: {'field': 'vehicle_interest', 'operator': 'contains', 'value': 'Premium'}
        if (isset($conditions['field'], $conditions['operator'], $conditions['value'])) {
            $fieldValue = data_get($data, $conditions['field']);

            switch ($conditions['operator']) {
                case 'equals':
                    return $fieldValue === $conditions['value'];
                case 'contains':
                    return str_contains((string) $fieldValue, $conditions['value']);
                case 'starts_with':
                    return str_starts_with((string) $fieldValue, $conditions['value']);
                case 'ends_with':
                    return str_ends_with((string) $fieldValue, $conditions['value']);
                case 'in':
                    $values = is_array($conditions['value']) ? $conditions['value'] : [$conditions['value']];

                    return in_array($fieldValue, $values);
                case 'not_equals':
                    return $fieldValue !== $conditions['value'];
                case 'greater_than':
                    return (float) $fieldValue > (float) $conditions['value'];
                case 'less_than':
                    return (float) $fieldValue < (float) $conditions['value'];
                default:
                    return false;
            }
        }

        return false;
    }

    /**
     * Get sender, with auto-resolution if not set
     */
    public function getResolvedSender(): ?NotificationSender
    {
        if ($this->sender_id && $this->sender) {
            return $this->sender;
        }

        return app(\Appsolutely\AIO\Services\NotificationSenderService::class)
            ->getSenderForRule($this);
    }

    /**
     * Get usage statistics
     */
    public function getUsageStatsAttribute(): array
    {
        return [
            'total_sent' => $this->queueItems()->where('status', 'sent')->count(),
            'pending'    => $this->queueItems()->where('status', 'pending')->count(),
            'failed'     => $this->queueItems()->where('status', 'failed')->count(),
        ];
    }
}
