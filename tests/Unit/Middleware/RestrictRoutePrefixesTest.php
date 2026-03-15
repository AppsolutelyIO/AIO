<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\RestrictRoutePrefixes;
use Appsolutely\AIO\Services\Contracts\RouteRestrictionServiceInterface;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RestrictRoutePrefixesTest extends TestCase
{
    private RouteRestrictionServiceInterface|MockInterface $routeRestrictionService;

    private RestrictRoutePrefixes $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->routeRestrictionService = Mockery::mock(RouteRestrictionServiceInterface::class);
        $this->middleware              = new RestrictRoutePrefixes($this->routeRestrictionService);
    }

    private function makeRequest(string $path): Request
    {
        return Request::create('http://example.com/' . ltrim($path, '/'));
    }

    // --- Allowed prefixes ---

    public function test_passes_through_when_prefix_is_not_disabled(): void
    {
        $this->routeRestrictionService->shouldReceive('isPrefixDisabled')
            ->with('shop')
            ->andReturn(false);

        $request  = $this->makeRequest('/shop/products');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    public function test_passes_through_when_no_first_segment(): void
    {
        $request  = $this->makeRequest('/');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Disabled prefixes ---

    public function test_aborts_404_when_prefix_is_disabled(): void
    {
        $this->routeRestrictionService->shouldReceive('isPrefixDisabled')
            ->with('api')
            ->andReturn(true);

        $this->expectException(NotFoundHttpException::class);

        $request = $this->makeRequest('/api/users');
        $this->middleware->handle($request, fn ($r) => response('OK'));
    }

    // --- Various path segments ---

    public function test_checks_only_first_segment(): void
    {
        $this->routeRestrictionService->shouldReceive('isPrefixDisabled')
            ->with('blog')
            ->once()
            ->andReturn(false);

        $request  = $this->makeRequest('/blog/post/123');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_disabled_prefix_with_nested_path_still_aborts(): void
    {
        $this->routeRestrictionService->shouldReceive('isPrefixDisabled')
            ->with('admin')
            ->andReturn(true);

        $this->expectException(NotFoundHttpException::class);

        $request = $this->makeRequest('/admin/dashboard/settings');
        $this->middleware->handle($request, fn ($r) => response('OK'));
    }
}
