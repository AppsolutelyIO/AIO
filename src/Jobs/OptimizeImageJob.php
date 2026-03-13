<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Jobs;

use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Services\Contracts\ImageOptimizationServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(public FileAttachment $attachment) {}

    /**
     * Execute the job.
     */
    public function handle(ImageOptimizationServiceInterface $service): void
    {
        $service->optimizeForAttachment($this->attachment);
    }
}
