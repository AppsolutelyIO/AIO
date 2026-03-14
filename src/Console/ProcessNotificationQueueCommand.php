<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Services\Contracts\NotificationQueueServiceInterface;
use Illuminate\Console\Command;

final class ProcessNotificationQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:process-queue
                            {--limit=100 : Maximum number of notifications to process}
                            {--once : Process once and exit}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending notifications from notification_queue table and dispatch to Laravel queue';

    /**
     * Execute the console command.
     */
    public function handle(NotificationQueueServiceInterface $queueService): int
    {
        $limit = (int) $this->option('limit');
        $once  = $this->option('once');

        if ($once) {
            return $this->processOnce($queueService, $limit);
        }

        return $this->processLoop($queueService, $limit);
    }

    /**
     * Process once and exit
     */
    protected function processOnce(NotificationQueueServiceInterface $queueService, int $limit): int
    {
        $this->info('Processing notification queue...');

        $processed = $queueService->processPending($limit);

        if ($processed > 0) {
            $this->info("Processed {$processed} notification(s) and dispatched to Laravel queue.");
            $this->comment('Run "php artisan queue:work --queue=notifications,default" to send the emails.');
        } else {
            $this->info('No pending notifications to process.');
        }

        return self::SUCCESS;
    }

    /**
     * Process in continuous loop
     */
    protected function processLoop(NotificationQueueServiceInterface $queueService, int $limit): int
    {
        $this->info('Starting notification queue processor (Ctrl+C to stop)...');

        while (true) {
            try {
                $processed = $queueService->processPending($limit);

                if ($processed > 0) {
                    $this->line('[' . now()->format('Y-m-d H:i:s') . "] Processed {$processed} notification(s)");
                }

                // Sleep for 5 seconds before next iteration
                sleep(5);
            } catch (\Exception $e) {
                $this->error('Error processing notifications: ' . $e->getMessage());
                $this->error($e->getTraceAsString());

                // Sleep longer on error
                sleep(10);
            }
        }

        return self::SUCCESS;
    }
}
