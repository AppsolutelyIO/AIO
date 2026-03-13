<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Models\NotificationQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class NotificationQueueRepository extends BaseRepository
{
    public function model(): string
    {
        return NotificationQueue::class;
    }

    /**
     * Get pending queue items ready to send
     */
    public function getPendingToSend(): Collection
    {
        $maxRetries = config('notifications.max_retry_attempts', 3);

        return $this->model->newQuery()->where('status', NotificationQueueStatus::Pending)
            ->where('scheduled_at', '<=', now())
            ->where(function ($query) use ($maxRetries) {
                $query->where('attempts', '<', $maxRetries)
                    ->orWhereNull('attempts');
            })
            ->with(['rule', 'template'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get failed items that can be retried
     */
    public function getRetryable(): Collection
    {
        $maxRetries = config('notifications.max_retry_attempts', 3);

        return $this->model->newQuery()->where('status', NotificationQueueStatus::Failed)
            ->where('attempts', '<', $maxRetries)
            ->with(['rule', 'template'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * Get queue items by status with pagination
     */
    public function getByStatus(string $status, ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('notifications.default_per_page', 20);

        return $this->model->newQuery()->where('status', $status)
            ->with(['rule', 'template'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all queue items with pagination and filtering
     */
    public function getPaginated(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('notifications.default_per_page', 20);
        $query   = $this->model->newQuery()->with(['rule', 'template']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['template_id'])) {
            $query->where('template_id', $filters['template_id']);
        }

        if (! empty($filters['rule_id'])) {
            $query->where('rule_id', $filters['rule_id']);
        }

        if (! empty($filters['recipient_email'])) {
            $query->where('recipient_email', 'like', '%' . $filters['recipient_email'] . '%');
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create queue item
     */
    public function createQueueItem(array $data): NotificationQueue
    {
        // Set default scheduled_at if not provided
        if (empty($data['scheduled_at'])) {
            $data['scheduled_at'] = now();
        }

        // Ensure proper data types
        if (isset($data['variables']) && is_string($data['variables'])) {
            $data['variables'] = json_decode($data['variables'], true);
        }

        if (isset($data['metadata']) && is_string($data['metadata'])) {
            $data['metadata'] = json_decode($data['metadata'], true);
        }

        return $this->create($data);
    }

    /**
     * Update queue item status
     */
    public function updateStatus(int $id, string $status, ?string $errorMessage = null): NotificationQueue
    {
        $data = ['status' => $status];

        if ($status === NotificationQueueStatus::Sent->value) {
            $data['sent_at'] = now();
        } elseif ($status === NotificationQueueStatus::Failed->value && $errorMessage) {
            $data['error_message'] = $errorMessage;
        }

        $this->update($data, $id);

        return $this->find($id);
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(int $id): NotificationQueue
    {
        $maxRetries = config('notifications.max_retry_attempts', 3);
        $item       = $this->find($id);
        $retryCount = ($item->attempts ?? 0) + 1;

        $this->update([
            'attempts' => $retryCount,
            'status'   => $retryCount >= $maxRetries ? NotificationQueueStatus::Failed : NotificationQueueStatus::Pending,
        ], $id);

        return $this->find($id);
    }

    /**
     * Get statistics using a single aggregated query instead of 7 separate queries
     */
    public function getStatistics(): array
    {
        $now        = now();
        $monthStart = $now->copy()->startOfMonth()->toDateTimeString();
        $monthEnd   = $now->copy()->endOfMonth()->toDateTimeString();
        $weekStart  = $now->copy()->startOfWeek()->toDateTimeString();
        $weekEnd    = $now->copy()->endOfWeek()->toDateTimeString();

        $stats = $this->model->newQuery()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending', [NotificationQueueStatus::Pending->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent', [NotificationQueueStatus::Sent->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed', [NotificationQueueStatus::Failed->value])
            ->selectRaw('SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today', [$now->toDateString()])
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_week', [$weekStart, $weekEnd])
            ->selectRaw('SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_month', [$monthStart, $monthEnd])
            ->first();

        return [
            'total'      => (int) $stats->total,
            'pending'    => (int) $stats->pending,
            'sent'       => (int) $stats->sent,
            'failed'     => (int) $stats->failed,
            'today'      => (int) $stats->today,
            'this_week'  => (int) $stats->this_week,
            'this_month' => (int) $stats->this_month,
        ];
    }

    /**
     * Get daily statistics for chart
     */
    public function getDailyStats(int $days = 30): array
    {
        $stats = $this->model->newQuery()->selectRaw('DATE(created_at) as date, status, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date          = now()->subDays($i)->format('Y-m-d');
            $result[$date] = [
                'pending' => 0,
                'sent'    => 0,
                'failed'  => 0,
            ];
        }

        foreach ($stats as $stat) {
            if (isset($result[$stat->date])) {
                $statusKey                       = $stat->status instanceof NotificationQueueStatus ? $stat->status->value : $stat->status;
                $result[$stat->date][$statusKey] = $stat->count;
            }
        }

        return $result;
    }

    /**
     * Get items by template
     */
    public function getByTemplate(int $templateId): Collection
    {
        return $this->model->newQuery()->where('template_id', $templateId)
            ->with('rule')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get items by rule
     */
    public function getByRule(int $ruleId): Collection
    {
        return $this->model->newQuery()->where('rule_id', $ruleId)
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get items scheduled for future
     */
    public function getScheduled(): Collection
    {
        return $this->model->newQuery()->where('status', NotificationQueueStatus::Pending)
            ->where('scheduled_at', '>', now())
            ->with(['rule', 'template'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Cancel scheduled items
     */
    public function cancelScheduled(array $ids): int
    {
        return $this->model->newQuery()->whereIn('id', $ids)
            ->where('status', NotificationQueueStatus::Pending)
            ->update(['status' => NotificationQueueStatus::Cancelled]);
    }

    /**
     * Retry failed items
     */
    public function retryFailed(array $ids = []): int
    {
        $maxRetries = config('notifications.max_retry_attempts', 3);
        $query      = $this->model->newQuery()->where('status', NotificationQueueStatus::Failed)
            ->where('attempts', '<', $maxRetries);

        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        return $query->update([
            'status'        => NotificationQueueStatus::Pending,
            'scheduled_at'  => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Retry a specific notification
     */
    public function retry(int $id): bool
    {
        $maxRetries = config('notifications.max_retry_attempts', 3);
        $updated    = $this->model->newQuery()->where('id', $id)
            ->where('status', NotificationQueueStatus::Failed)
            ->where('attempts', '<', $maxRetries)
            ->update([
                'status'        => NotificationQueueStatus::Pending,
                'scheduled_at'  => now(),
                'error_message' => null,
            ]);

        return $updated > 0;
    }

    /**
     * Cancel a specific notification
     */
    public function cancel(int $id): bool
    {
        $updated = $this->model->newQuery()->where('id', $id)
            ->whereIn('status', [NotificationQueueStatus::Pending, NotificationQueueStatus::Processing])
            ->update(['status' => NotificationQueueStatus::Cancelled]);

        return $updated > 0;
    }

    /**
     * Clean old sent items
     */
    public function cleanOldSent(?int $daysOld = null): int
    {
        $daysOld = $daysOld ?? config('notifications.cleanup_sent_after_days', 90);

        return $this->model->newQuery()->where('status', NotificationQueueStatus::Sent)
            ->where('sent_at', '<', now()->subDays($daysOld))
            ->delete();
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        return $this->model->newQuery()->with(['rule', 'template'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if notification exists for entry and rule
     */
    public function hasNotificationForEntryAndRule(int $entryId, int $ruleId): bool
    {
        return $this->model->newQuery()
            ->where('form_entry_id', $entryId)
            ->where('rule_id', $ruleId)
            ->exists();
    }

    /**
     * Count notifications for entry and rule
     */
    public function countForEntryAndRule(int $entryId, int $ruleId): int
    {
        return $this->model->newQuery()
            ->where('form_entry_id', $entryId)
            ->where('rule_id', $ruleId)
            ->count();
    }
}
