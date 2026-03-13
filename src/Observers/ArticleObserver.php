<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Observers;

use Appsolutely\AIO\Events\ArticleCreated;
use Appsolutely\AIO\Events\ArticleDeleted;
use Appsolutely\AIO\Events\ArticleUpdated;
use Appsolutely\AIO\Models\Article;

/**
 * Observer for Article model events
 *
 * Dispatches domain events when articles are created, updated, or deleted.
 * Listeners handle side effects like cache clearing.
 */
final class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        event(new ArticleCreated($article));
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        event(new ArticleUpdated($article));
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        event(new ArticleDeleted($article));
    }
}
