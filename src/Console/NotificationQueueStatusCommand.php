<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class NotificationQueueStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:status';

    /**
     * The console command description.
     */
    protected $description = 'Show notification queue status and statistics';

    /**
     * Execute the console command.
     */
    public function handle(NotificationQueueRepository $queueRepository): int
    {
        $this->info('=== Notification Queue Status ===');
        $this->newLine();

        // Get statistics from notification_queue table
        $stats = $queueRepository->getStatistics();

        // Get processing count
        $processingCount = DB::table('notification_queue')->where('status', 'processing')->count();

        $this->table(
            ['Status', 'Count'],
            [
                ['Total', $stats['total']],
                ['Pending', $stats['pending']],
                ['Processing', $processingCount],
                ['Sent', $stats['sent']],
                ['Failed', $stats['failed']],
                ['Today', $stats['today']],
                ['This Week', $stats['this_week']],
                ['This Month', $stats['this_month']],
            ]
        );

        // Show pending notifications ready to send
        $readyToSend = DB::table('notification_queue')
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->count();

        if ($readyToSend > 0) {
            $this->warn("⚠️  {$readyToSend} notification(s) ready to send NOW");
            $this->comment('Run: php artisan notifications:process-and-send');
        } else {
            $this->info('✓ No notifications waiting to be processed');
        }

        $this->newLine();

        // Show Laravel jobs queue status
        $this->info('=== Laravel Jobs Queue Status ===');
        $this->newLine();

        $jobsCount       = DB::table('jobs')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();

        $this->table(
            ['Queue', 'Count'],
            [
                ['Pending Jobs', $jobsCount],
                ['Failed Jobs', $failedJobsCount],
            ]
        );

        if ($jobsCount > 0) {
            $this->warn("⚠️  {$jobsCount} job(s) waiting in Laravel queue");
            $this->comment('Run: php artisan queue:work --queue=notifications,default --stop-when-empty');
        } else {
            $this->info('✓ No jobs waiting in Laravel queue');
        }

        if ($failedJobsCount > 0) {
            $this->newLine();
            $this->error("⚠️  {$failedJobsCount} failed job(s)");
            $this->comment('Run: php artisan queue:retry all');
        }

        return self::SUCCESS;
    }
}
