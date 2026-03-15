<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Observers;

use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Observers\PageBlockSettingObserver;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class PageBlockSettingObserverTest extends TestCase
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

        $observer = app(PageBlockSettingObserver::class);
        $observer->saved(new PageBlockSetting());

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

        $observer = app(PageBlockSettingObserver::class);
        $observer->deleted(new PageBlockSetting());

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

        $observer = app(PageBlockSettingObserver::class);
        $observer->saved(new PageBlockSetting());
        $observer->deleted(new PageBlockSetting());

        $this->assertSame(2, $callCount);
    }
}
