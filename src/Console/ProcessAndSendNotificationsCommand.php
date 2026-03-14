<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Services\Contracts\NotificationQueueServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class ProcessAndSendNotificationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:process-and-send
                            {--limit=100 : Maximum number of notifications to process}';

    /**
     * The console command description.
     */
    protected $description = 'Process notification queue AND send emails (complete workflow)';

    /**
     * Execute the console command.
     */
    public function handle(NotificationQueueServiceInterface $queueService): int
    {
        $limit = (int) $this->option('limit');

        // Step 1: Process notification_queue table and dispatch jobs
        $this->info('Step 1: Processing notification queue...');
        $processed = $queueService->processPending($limit);

        if ($processed === 0) {
            $this->info('No pending notifications to process.');

            return self::SUCCESS;
        }

        $this->info("✓ Dispatched {$processed} notification(s) to Laravel queue");

        // Step 2: Process Laravel jobs queue
        $this->newLine();
        $this->info('Step 2: Running queue worker to send emails...');
        $this->comment('Processing jobs from Laravel queue...');

        // Run queue:work with --stop-when-empty flag
        $exitCode = Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries'           => 3,
            '--timeout'         => 90,
        ]);

        $this->newLine();
        if ($exitCode === 0) {
            $this->info('✓ All notifications processed and sent successfully!');
        } else {
            $this->error('Queue worker exited with errors. Check logs for details.');
        }

        return $exitCode;
    }
}
