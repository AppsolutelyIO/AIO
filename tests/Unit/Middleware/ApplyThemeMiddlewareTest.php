<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\ApplyThemeMiddleware;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;

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

    public function test_calls_ensure_setup_on_every_request(): void
    {
        $this->themeService->shouldReceive('ensureSetup')->once();

        $request  = $this->makeRequest('/test');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_calls_ensure_setup_for_admin_path(): void
    {
        $this->themeService->shouldReceive('ensureSetup')->once();

        $request  = $this->makeRequest('/admin/dashboard');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_passes_response_through(): void
    {
        $this->themeService->shouldReceive('ensureSetup')->once();

        $request  = $this->makeRequest('/');
        $response = $this->middleware->handle($request, fn ($r) => response('themed content', 201));

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('themed content', $response->getContent());
    }
}
