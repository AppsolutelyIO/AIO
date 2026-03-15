<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\ApplyThemeMiddleware;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Appsolutely\AIO\Tests\TestCase;

final class ApplyThemeMiddlewareTest extends TestCase
{
    private ThemeServiceInterface|MockInterface $themeService;

    private ApplyThemeMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->themeService = Mockery::mock(ThemeServiceInterface::class);
        $this->middleware   = new ApplyThemeMiddleware($this->themeService);
    }

    private function makeRequest(string $path = '/'): Request
    {
        return Request::create('http://example.com/' . ltrim($path, '/'));
    }

    // --- Theme applied ---

    public function test_sets_up_theme_when_resolved_and_applicable(): void
    {
        $this->themeService->shouldReceive('resolveThemeName')->once()->andReturn('june');
        $this->themeService->shouldReceive('shouldApplyTheme')->with('test')->once()->andReturn(true);
        $this->themeService->shouldReceive('getParentTheme')->once()->andReturn('default');
        $this->themeService->shouldReceive('setupTheme')->with('june', 'default')->once();

        $request  = $this->makeRequest('/test');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- No theme name resolved ---

    public function test_skips_theme_when_no_theme_resolved(): void
    {
        $this->themeService->shouldReceive('resolveThemeName')->once()->andReturn(null);
        $this->themeService->shouldNotReceive('shouldApplyTheme');
        $this->themeService->shouldNotReceive('setupTheme');

        $request  = $this->makeRequest('/about');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Theme not applicable for path ---

    public function test_skips_theme_when_path_not_applicable(): void
    {
        $this->themeService->shouldReceive('resolveThemeName')->once()->andReturn('june');
        $this->themeService->shouldReceive('shouldApplyTheme')->with('admin/dashboard')->once()->andReturn(false);
        $this->themeService->shouldNotReceive('setupTheme');

        $request  = $this->makeRequest('/admin/dashboard');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Parent theme null ---

    public function test_sets_up_theme_with_null_parent(): void
    {
        $this->themeService->shouldReceive('resolveThemeName')->once()->andReturn('tabler');
        $this->themeService->shouldReceive('shouldApplyTheme')->once()->andReturn(true);
        $this->themeService->shouldReceive('getParentTheme')->once()->andReturn(null);
        $this->themeService->shouldReceive('setupTheme')->with('tabler', null)->once();

        $request  = $this->makeRequest('/');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Response passthrough ---

    public function test_passes_response_through(): void
    {
        $this->themeService->shouldReceive('resolveThemeName')->andReturn(null);

        $request  = $this->makeRequest('/');
        $response = $this->middleware->handle($request, fn ($r) => response('themed content', 201));

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('themed content', $response->getContent());
    }
}
