<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Events;

use Appsolutely\AIO\Models\Article;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when an article is deleted
 */
final class ArticleDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Article $article
    ) {}
}
