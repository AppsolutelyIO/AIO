<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Events;

use Appsolutely\AIO\Models\Page;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a page is created
 */
final class PageCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Page $page
    ) {}
}
