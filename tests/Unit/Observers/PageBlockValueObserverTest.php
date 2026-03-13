<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Observers;

use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Observers\PageBlockValueObserver;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Appsolutely\AIO\Tests\TestCase;

final class PageBlockValueObserverTest extends TestCase
{
    // --- saved ---

    public function test_saved_clears_slug_alias_cache(): void
    {
        $called = false;
        $cache  = $this->mock(CacheRepository::class);
        $cache->shouldReceive('forget')->once()->with('page_slug_aliases')
            ->andReturnUsing(function () use (&$called) {
                $called = true;

                return true;
            });

        $observer = app(PageBlockValueObserver::class);
        $observer->saved(new PageBlockValue());

        $this->assertTrue($called);
    }

    // --- deleted ---

    public function test_deleted_clears_slug_alias_cache(): void
    {
        $called = false;
        $cache  = $this->mock(CacheRepository::class);
        $cache->shouldReceive('forget')->once()->with('page_slug_aliases')
            ->andReturnUsing(function () use (&$called) {
                $called = true;

                return true;
            });

        $observer = app(PageBlockValueObserver::class);
        $observer->deleted(new PageBlockValue());

        $this->assertTrue($called);
    }

    // --- Both events clear cache ---

    public function test_saved_and_deleted_both_clear_cache(): void
    {
        $callCount = 0;
        $cache     = $this->mock(CacheRepository::class);
        $cache->shouldReceive('forget')->twice()->with('page_slug_aliases')
            ->andReturnUsing(function () use (&$callCount) {
                $callCount++;

                return true;
            });

        $observer = app(PageBlockValueObserver::class);
        $observer->saved(new PageBlockValue());
        $observer->deleted(new PageBlockValue());

        $this->assertSame(2, $callCount);
    }
}
