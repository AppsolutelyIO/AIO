<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Observers;

use Appsolutely\AIO\Events\ArticleCreated;
use Appsolutely\AIO\Events\ArticleDeleted;
use Appsolutely\AIO\Events\ArticleUpdated;
use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Observers\ArticleObserver;
use Illuminate\Support\Facades\Event;
use Appsolutely\AIO\Tests\TestCase;

final class ArticleObserverTest extends TestCase
{
    private ArticleObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->observer = new ArticleObserver();
    }

    // --- created ---

    public function test_created_dispatches_article_created_event(): void
    {
        Event::fake([ArticleCreated::class]);

        $article = new Article(['title' => 'Test Article']);
        $this->observer->created($article);

        Event::assertDispatched(ArticleCreated::class, function (ArticleCreated $event) use ($article) {
            return $event->article === $article;
        });
    }

    // --- updated ---

    public function test_updated_dispatches_article_updated_event(): void
    {
        Event::fake([ArticleUpdated::class]);

        $article = new Article(['title' => 'Updated Article']);
        $this->observer->updated($article);

        Event::assertDispatched(ArticleUpdated::class, function (ArticleUpdated $event) use ($article) {
            return $event->article === $article;
        });
    }

    // --- deleted ---

    public function test_deleted_dispatches_article_deleted_event(): void
    {
        Event::fake([ArticleDeleted::class]);

        $article = new Article(['title' => 'Deleted Article']);
        $this->observer->deleted($article);

        Event::assertDispatched(ArticleDeleted::class, function (ArticleDeleted $event) use ($article) {
            return $event->article === $article;
        });
    }

    // --- Event dispatch count ---

    public function test_each_lifecycle_event_dispatches_exactly_once(): void
    {
        Event::fake([ArticleCreated::class, ArticleUpdated::class, ArticleDeleted::class]);

        $article = new Article(['title' => 'Test']);

        $this->observer->created($article);
        $this->observer->updated($article);
        $this->observer->deleted($article);

        Event::assertDispatchedTimes(ArticleCreated::class, 1);
        Event::assertDispatchedTimes(ArticleUpdated::class, 1);
        Event::assertDispatchedTimes(ArticleDeleted::class, 1);
    }
}
