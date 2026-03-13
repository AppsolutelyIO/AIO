<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface NotificationQueueServiceInterface
{
    /**
     * Process pending notifications
     *
     * @param  int  $limit  Maximum number of notifications to process
     * @return int Number of notifications processed
     */
    public function processPending(int $limit = 100): int;
}
