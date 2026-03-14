<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Services\Contracts\NotificationServiceInterface;
use Appsolutely\AIO\Services\FormFieldFormatterService;
use Illuminate\Console\Command;

final class ResyncFormEntryNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:resync-form-entries
                            {--form= : Specific form slug to resync}
                            {--form-id= : Specific form ID to resync}
                            {--entry-id= : Specific entry ID to resync}
                            {--entry-ids= : Multiple entry IDs to resync (comma-separated: 1,2,3)}
                            {--entry-id-from= : Resync entries with ID greater than or equal to this value}
                            {--entry-id-to= : Resync entries with ID less than or equal to this value}
                            {--from-date= : Resync entries from this date (Y-m-d format)}
                            {--to-date= : Resync entries up to this date (Y-m-d format)}
                            {--dry-run : Show what would be resynced without actually doing it}
                            {--force : Force resync even if notifications already exist}';

    /**
     * The console command description.
     */
    protected $description = 'Resync form entries that are missing notification queue entries with flexible filtering';

    /**
     * Execute the console command.
     */
    public function handle(
        NotificationRuleRepository $ruleRepository,
        FormEntryRepository $entryRepository,
        FormRepository $formRepository,
        NotificationQueueRepository $queueRepository,
        NotificationServiceInterface $notificationService,
        FormFieldFormatterService $fieldFormatter
    ): int {
        $dryRun       = $this->option('dry-run');
        $force        = $this->option('force');
        $formSlug     = $this->option('form') ? (string) $this->option('form') : null;
        $formId       = $this->option('form-id') ? (string) $this->option('form-id') : null;
        $entryId      = $this->option('entry-id') ? (string) $this->option('entry-id') : null;
        $entryIds     = $this->option('entry-ids') ? (string) $this->option('entry-ids') : null;
        $entryIdFrom  = $this->option('entry-id-from') ? (string) $this->option('entry-id-from') : null;
        $entryIdTo    = $this->option('entry-id-to') ? (string) $this->option('entry-id-to') : null;
        $fromDate     = $this->option('from-date') ? (string) $this->option('from-date') : null;
        $toDate       = $this->option('to-date') ? (string) $this->option('to-date') : null;

        // Parse entry IDs if provided as comma-separated string
        $entryIdsArray = null;
        if ($entryIds) {
            $entryIdsArray = array_map('trim', explode(',', $entryIds));
            $entryIdsArray = array_filter($entryIdsArray, 'is_numeric');
            $entryIdsArray = array_map('intval', $entryIdsArray);
        }

        $this->info('🔄 Starting form entry notification resync...');
        $this->newLine();

        // Display active filters
        $this->displayActiveFilters($formSlug, $formId, $entryId, $entryIdsArray, $entryIdFrom, $entryIdTo, $fromDate, $toDate);

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No notifications will be created');
            $this->newLine();
        }

        // Get all active form submission notification rules
        $rules = $ruleRepository->getByTriggerType('form_submission')
            ->filter(fn ($rule) => $rule->status === Status::ACTIVE);

        if ($rules->isEmpty()) {
            $this->warn('No active form submission notification rules found.');

            return self::FAILURE;
        }

        $this->info("Found {$rules->count()} active notification rule(s) for form submissions.");
        $this->newLine();

        $totalProcessed = 0;
        $totalResynced  = 0;
        $totalSkipped   = 0;

        foreach ($rules as $rule) {
            $this->line("📋 Processing rule: <fg=cyan>{$rule->name}</> (ID: {$rule->id})");
            $this->line('   Trigger: ' . $rule->trigger_type->value . ' → ' . $rule->trigger_reference);

            // Get entries for this rule
            $entries = $this->getEntriesForRule(
                $rule,
                $entryRepository,
                $formSlug,
                $formId,
                $entryId,
                $entryIdsArray,
                $entryIdFrom,
                $entryIdTo,
                $fromDate,
                $toDate
            );

            if ($entries->isEmpty()) {
                $this->line('   <fg=yellow>No matching entries found</>');
                $this->newLine();

                continue;
            }

            $this->line("   Found {$entries->count()} matching entry/entries");

            $ruleResynced = 0;
            $ruleSkipped  = 0;

            foreach ($entries as $entry) {
                $totalProcessed++;

                // Check if notifications already exist for this entry and rule
                $hasNotifications = $queueRepository->hasNotificationForEntryAndRule($entry->id, $rule->id);

                if ($hasNotifications && ! $force) {
                    $ruleSkipped++;

                    continue;
                }

                if ($dryRun) {
                    $this->line("   → Would resync: Entry #{$entry->id} ({$entry->getUserName()}) - {$entry->email}");
                    $ruleResynced++;
                } else {
                    // Trigger the notification
                    try {
                        // Load form with fields
                        $form = $entry->form()->with('fields')->first();

                        if (! $form) {
                            $this->error("   ✗ Entry #{$entry->id}: Form not found");

                            continue;
                        }

                        // Prepare notification data using formatter service
                        $notificationData = $fieldFormatter->prepareNotificationData($form, $entry);

                        // Trigger notification
                        $notificationService->trigger(
                            'form_submission',
                            $form->slug,
                            $notificationData
                        );

                        $this->line("   ✓ Resynced: Entry #{$entry->id} ({$entry->getUserName()}) - {$entry->email}");
                        $ruleResynced++;
                        $totalResynced++;
                    } catch (\Exception $e) {
                        $this->error("   ✗ Failed to resync Entry #{$entry->id}: {$e->getMessage()}");
                    }
                }
            }

            $totalSkipped += $ruleSkipped;

            if ($ruleSkipped > 0) {
                $this->line("   <fg=yellow>Skipped {$ruleSkipped} entry/entries (notifications already exist)</>");
            }

            if ($ruleResynced > 0) {
                $this->line("   <fg=green>Resynced: {$ruleResynced} entry/entries</>");
            }

            $this->newLine();
        }

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Resync Summary:');
        $this->info("   Total Processed: {$totalProcessed}");
        $this->info("   Resynced: <fg=green>{$totalResynced}</>");
        $this->info("   Skipped: <fg=yellow>{$totalSkipped}</>");

        if ($dryRun) {
            $this->newLine();
            $this->warn('This was a dry run. Run without --dry-run to actually resync.');
        } elseif ($totalResynced > 0) {
            $this->newLine();
            $this->comment('💡 Tip: Run "php artisan notifications:process-queue" to process the notifications.');
        }

        return self::SUCCESS;
    }

    /**
     * Get entries for a specific rule based on filters
     */
    private function getEntriesForRule(
        $rule,
        FormEntryRepository $entryRepository,
        ?string $formSlug,
        ?string $formId,
        ?string $entryId,
        ?array $entryIdsArray,
        ?string $entryIdFrom,
        ?string $entryIdTo,
        ?string $fromDate,
        ?string $toDate
    ) {
        // If specific entry ID provided, get just that one
        if ($entryId) {
            $entry = $entryRepository->find((int) $entryId);

            return $entry ? collect([$entry]) : collect([]);
        }

        // If multiple entry IDs provided, get all of them
        if ($entryIdsArray && ! empty($entryIdsArray)) {
            return $entryRepository->getByIds($entryIdsArray);
        }

        // Build filters and fetch all pages (paginated) so callers get a single collection
        $filters = [
            'form_id'           => $formId,
            'form_slug'         => $formSlug,
            'trigger_reference' => $rule->trigger_reference,
            'entry_id_from'     => $entryIdFrom,
            'entry_id_to'       => $entryIdTo,
            'from_date'         => $fromDate,
            'to_date'           => $toDate,
            'per_page'          => 100,
        ];

        $entries = collect();
        $page    = 1;

        do {
            $paginator = $entryRepository->getEntriesByFiltersPaginated(array_merge($filters, ['page' => $page]));
            $entries   = $entries->merge($paginator->items());
            $page++;
        } while ($page <= $paginator->lastPage());

        return $entries;
    }

    /**
     * Display active filters to the user
     */
    private function displayActiveFilters(
        ?string $formSlug,
        ?string $formId,
        ?string $entryId,
        ?array $entryIdsArray,
        ?string $entryIdFrom,
        ?string $entryIdTo,
        ?string $fromDate,
        ?string $toDate
    ): void {
        $filters = [];

        if ($formSlug) {
            $filters[] = "Form Slug: <fg=cyan>{$formSlug}</>";
        }

        if ($formId) {
            $filters[] = "Form ID: <fg=cyan>{$formId}</>";
        }

        if ($entryId) {
            $filters[] = "Entry ID: <fg=cyan>{$entryId}</>";
        }

        if ($entryIdsArray && ! empty($entryIdsArray)) {
            $count     = count($entryIdsArray);
            $idsString = implode(', ', $entryIdsArray);
            if ($count <= 5) {
                $filters[] = "Entry IDs: <fg=cyan>{$idsString}</>";
            } else {
                $preview   = implode(', ', array_slice($entryIdsArray, 0, 5));
                $filters[] = "Entry IDs: <fg=cyan>{$preview}...</> (total: {$count})";
            }
        }

        if ($entryIdFrom && $entryIdTo) {
            $filters[] = "Entry ID Range: <fg=cyan>{$entryIdFrom}</> to <fg=cyan>{$entryIdTo}</>";
        } elseif ($entryIdFrom) {
            $filters[] = "Entry ID: <fg=cyan>≥ {$entryIdFrom}</>";
        } elseif ($entryIdTo) {
            $filters[] = "Entry ID: <fg=cyan>≤ {$entryIdTo}</>";
        }

        if ($fromDate && $toDate) {
            $filters[] = "Date Range: <fg=cyan>{$fromDate}</> to <fg=cyan>{$toDate}</>";
        } elseif ($fromDate) {
            $filters[] = "From Date: <fg=cyan>{$fromDate}</>";
        } elseif ($toDate) {
            $filters[] = "To Date: <fg=cyan>{$toDate}</>";
        }

        if (! empty($filters)) {
            $this->info('📋 Active Filters:');
            foreach ($filters as $filter) {
                $this->line("   • {$filter}");
            }
            $this->newLine();
        } else {
            $this->info('📋 No filters applied - processing all entries matching active rules');
            $this->newLine();
        }
    }
}
