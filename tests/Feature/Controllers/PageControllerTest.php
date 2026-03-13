<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature\Controllers;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\Contracts\GeneralPageServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Appsolutely\AIO\Tests\TestCase;

final class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_page_view_for_published_page(): void
    {
        $page = Page::factory()->create([
            'slug'         => 'test-page',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $generalPage = new GeneralPage($page);

        $generalPageService = Mockery::mock(GeneralPageServiceInterface::class);
        $generalPageService->shouldReceive('resolvePageWithCaching')
            ->once()
            ->with('test-page')
            ->andReturn($generalPage);

        $this->app->instance(GeneralPageServiceInterface::class, $generalPageService);

        $response = $this->get('/test-page');

        $response->assertStatus(200);
        // Avoid assertViewIs/assertViewHas: Livewire asset injection can replace the View with rendered HTML
    }

    public function test_show_returns_404_for_non_existent_page(): void
    {
        $generalPageService = Mockery::mock(GeneralPageServiceInterface::class);
        $generalPageService->shouldReceive('resolvePageWithCaching')
            ->once()
            ->with('non-existent-page')
            ->andReturn(null);

        $this->app->instance(GeneralPageServiceInterface::class, $generalPageService);

        $response = $this->get('/non-existent-page');

        $response->assertStatus(404);
    }

    public function test_show_handles_home_route_with_null_slug(): void
    {
        $page = Page::factory()->create([
            'slug'         => '/',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $generalPage = new GeneralPage($page);

        $generalPageService = Mockery::mock(GeneralPageServiceInterface::class);
        $generalPageService->shouldReceive('resolvePageWithCaching')
            ->once()
            ->with(null)
            ->andReturn($generalPage);

        $this->app->instance(GeneralPageServiceInterface::class, $generalPageService);

        $response = $this->get('/');

        $response->assertStatus(200);
        // Avoid assertViewIs: Livewire asset injection can replace the View with rendered HTML
    }

    public function test_show_handles_empty_slug(): void
    {
        $page = Page::factory()->create([
            'slug'         => '/',
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
        ]);

        $generalPage = new GeneralPage($page);

        $generalPageService = Mockery::mock(GeneralPageServiceInterface::class);
        $generalPageService->shouldReceive('resolvePageWithCaching')
            ->once()
            ->with('')
            ->andReturn($generalPage);

        $this->app->instance(GeneralPageServiceInterface::class, $generalPageService);

        $response = $this->get('');

        $response->assertStatus(200);
    }
}
