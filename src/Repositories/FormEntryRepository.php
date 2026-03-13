<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Models\FormEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final class FormEntryRepository extends BaseRepository
{
    public function model(): string
    {
        return FormEntry::class;
    }

    /**
     * Get entries for a specific form
     */
    public function getEntriesByForm(int $formId, ?bool $includeSpam = null): Collection
    {
        // Default to excluding spam unless explicitly requested
        $includeSpam = $includeSpam ?? config('forms.export.include_spam', false);

        $query = $this->model->newQuery()
            ->where('form_id', $formId)
            ->with(['form', 'user'])
            ->orderBy('submitted_at', 'desc');

        if (! $includeSpam) {
            $query->where('is_spam', FormEntrySpamStatus::Valid);
        }

        return $query->get();
    }

    /**
     * Get paginated entries for a form
     */
    public function getPaginatedEntriesByForm(int $formId, ?int $perPage = null, bool $includeSpam = true): LengthAwarePaginator
    {
        $perPage = $perPage ?? config('forms.default_per_page', 15);
        $query   = $this->model->newQuery()
            ->where('form_id', $formId)
            ->with(['form', 'user'])
            ->orderBy('submitted_at', 'desc');

        if (! $includeSpam) {
            $query->where('is_spam', FormEntrySpamStatus::Valid);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get recent entries across all forms
     */
    public function getRecentEntries(int $limit = 10): Collection
    {
        return $this->model->newQuery()
            ->with(['form', 'user'])
            ->where('is_spam', FormEntrySpamStatus::Valid)
            ->orderBy('submitted_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get entries by user
     */
    public function getEntriesByUser(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->with(['form'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get entries by email
     */
    public function getEntriesByEmail(string $email): Collection
    {
        return $this->model->newQuery()
            ->where('email', $email)
            ->with(['form'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Mark entries as spam
     */
    public function markAsSpam(array $entryIds): int
    {
        return $this->model->newQuery()
            ->whereIn('id', $entryIds)
            ->update(['is_spam' => FormEntrySpamStatus::Spam]);
    }

    /**
     * Mark entries as not spam
     */
    public function markAsNotSpam(array $entryIds): int
    {
        return $this->model->newQuery()
            ->whereIn('id', $entryIds)
            ->update(['is_spam' => FormEntrySpamStatus::Valid]);
    }

    /**
     * Mark single entry as spam
     */
    public function markSingleAsSpam(int $entryId): bool
    {
        $entry = $this->findOrFail($entryId);
        $entry->markAsSpam();

        return true;
    }

    /**
     * Mark single entry as not spam
     */
    public function markSingleAsNotSpam(int $entryId): bool
    {
        $entry = $this->findOrFail($entryId);
        $entry->markAsNotSpam();

        return true;
    }

    /**
     * Get spam entries
     */
    public function getSpamEntries(?int $formId = null): Collection
    {
        $query = $this->model->newQuery()
            ->where('is_spam', FormEntrySpamStatus::Spam)
            ->with(['form', 'user'])
            ->orderBy('submitted_at', 'desc');

        if ($formId) {
            $query->where('form_id', $formId);
        }

        return $query->get();
    }

    /**
     * Create entry with automatic spam detection
     */
    public function createEntryWithSpamCheck(array $data): FormEntry
    {
        // Basic spam detection can be implemented here
        $data['is_spam']      = $this->detectSpam($data) ? FormEntrySpamStatus::Spam : FormEntrySpamStatus::Valid;
        $data['submitted_at'] = now();

        return $this->create($data);
    }

    /**
     * Get statistics for a form
     */
    public function getFormStats(int $formId): array
    {
        $validValue = FormEntrySpamStatus::Valid->value;

        $stats = $this->model->newQuery()
            ->where('form_id', $formId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_spam = ? THEN 1 ELSE 0 END) as valid', [$validValue])
            ->selectRaw('SUM(CASE WHEN is_spam = ? AND DATE(submitted_at) = ? THEN 1 ELSE 0 END) as today', [$validValue, today()->toDateString()])
            ->selectRaw('SUM(CASE WHEN is_spam = ? AND submitted_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as this_week', [$validValue, now()->startOfWeek(), now()->endOfWeek()])
            ->first();

        return [
            'total'     => (int) $stats->total,
            'valid'     => (int) $stats->valid,
            'spam'      => (int) $stats->total - (int) $stats->valid,
            'today'     => (int) $stats->today,
            'this_week' => (int) $stats->this_week,
        ];
    }

    /**
     * Basic spam detection
     *
     * Performs simple pattern matching to identify potential spam entries.
     * Can be enhanced with more sophisticated detection (e.g., machine learning, third-party services).
     */
    private function detectSpam(array $data): bool
    {
        // Step 1: Check for common spam keywords in all text fields
        // Combine all text content from form fields for keyword scanning
        $spamKeywords = config('forms.spam_detection.keywords', ['viagra', 'casino', 'lottery', 'prize', 'winner']);
        $content      = implode(' ', array_filter([
            $data['name'] ?? '',
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['email'] ?? '',
            // Extract all values from nested data array if present
            is_array($data['data']) ? implode(' ', array_values($data['data'])) : '',
        ]));

        // Normalize content to lowercase for case-insensitive matching
        $content = strtolower($content);

        // Check if any spam keyword appears in the content
        foreach ($spamKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }

        // Step 2: Validate email format (if enabled in config)
        // Invalid email addresses are often a sign of spam
        if (config('forms.spam_detection.validate_email', true) && isset($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        // No spam indicators found
        return false;
    }

    /**
     * Get valid entries for export
     */
    public function getValidEntriesForExport(?int $formId = null): Collection
    {
        $includeSpam = config('forms.export.include_spam', false);
        $maxEntries  = config('forms.export.max_entries', 10000);

        $query = $this->model->newQuery()
            ->with(['form', 'user'])
            ->orderBy('submitted_at', 'desc');

        // Filter spam based on config
        if (! $includeSpam) {
            $query->where('is_spam', FormEntrySpamStatus::Valid);
        }

        if ($formId) {
            $query->where('form_id', $formId);
        }

        // Limit results to prevent memory issues with large exports
        return $query->limit($maxEntries)->get();
    }

    /**
     * Count valid entries
     */
    public function countValid(): int
    {
        return $this->model->where('is_spam', FormEntrySpamStatus::Valid)->count();
    }

    /**
     * Count spam entries
     */
    public function countSpam(): int
    {
        return $this->model->where('is_spam', FormEntrySpamStatus::Spam)->count();
    }

    /**
     * Count valid entries by date range
     */
    public function countValidByDateRange($startDate, $endDate): int
    {
        if ($startDate === $endDate) {
            return $this->model->whereDate('submitted_at', $startDate)
                ->where('is_spam', FormEntrySpamStatus::Valid)
                ->count();
        }

        return $this->model->whereBetween('submitted_at', [$startDate, $endDate])
            ->where('is_spam', FormEntrySpamStatus::Valid)
            ->count();
    }

    /**
     * Get entries without notification queue entries
     * Useful for identifying entries that failed to trigger notifications
     */
    public function getEntriesWithoutNotifications(?int $formId = null, ?int $limit = null): Collection
    {
        $query = $this->model->newQuery()
            ->leftJoin('notification_queue', 'form_entries.id', '=', 'notification_queue.form_entry_id')
            ->whereNull('notification_queue.id')
            ->where('form_entries.is_spam', FormEntrySpamStatus::Valid)
            ->with(['form', 'user'])
            ->select('form_entries.*')
            ->orderBy('form_entries.submitted_at', 'desc');

        if ($formId) {
            $query->where('form_entries.form_id', $formId);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Check if entry has notifications
     */
    public function hasNotifications(int $entryId): bool
    {
        return $this->model->newQuery()
            ->where('id', $entryId)
            ->whereHas('notifications')
            ->exists();
    }

    /**
     * Get entries with notifications count
     */
    public function getEntriesWithNotificationsCount(?int $formId = null): Collection
    {
        $query = $this->model->newQuery()
            ->withCount('notifications')
            ->where('is_spam', FormEntrySpamStatus::Valid)
            ->with(['form', 'user'])
            ->orderBy('submitted_at', 'desc');

        if ($formId) {
            $query->where('form_id', $formId);
        }

        return $query->get();
    }

    /**
     * Get entries by multiple IDs
     */
    public function getByIds(array $entryIds): Collection
    {
        return $this->model->newQuery()
            ->whereIn('id', $entryIds)
            ->where('is_spam', FormEntrySpamStatus::Valid)
            ->with(['form', 'form.fields'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get entries by filters (paginated). Filters: form_id, form_slug, trigger_reference (for resync),
     * entry_id_from, entry_id_to, from_date, to_date; plus page, per_page (default 15, max 100).
     * Valid (non-spam) only.
     *
     * @param  array{form_id?: int, form_slug?: string, trigger_reference?: string, entry_id_from?: int, entry_id_to?: int, from_date?: string, to_date?: string, page?: int, per_page?: int}  $filters
     */
    public function getEntriesByFiltersPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $page    = (int) ($filters['page'] ?? 1);
        $perPage = max(1, min(100, $perPage));
        $page    = max(1, $page);

        $query = $this->model->newQuery()
            ->where('is_spam', FormEntrySpamStatus::Valid)
            ->with(['form', 'form.fields']);

        if (! empty($filters['form_id'])) {
            $query->where('form_id', (int) $filters['form_id']);
        } elseif (! empty($filters['form_slug'])) {
            $query->whereHas('form', function ($q) use ($filters) {
                $q->where('slug', $filters['form_slug']);
            });
        } elseif (! empty($filters['trigger_reference']) && $filters['trigger_reference'] !== '*') {
            $query->whereHas('form', function ($q) use ($filters) {
                $q->where('slug', $filters['trigger_reference']);
            });
        }

        if (! empty($filters['entry_id_from'])) {
            $query->where('id', '>=', (int) $filters['entry_id_from']);
        }
        if (! empty($filters['entry_id_to'])) {
            $query->where('id', '<=', (int) $filters['entry_id_to']);
        }
        if (! empty($filters['from_date'])) {
            $query->whereDate('submitted_at', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('submitted_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('submitted_at', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }
}
