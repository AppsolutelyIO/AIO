<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\RestrictAdminDomainToAdminRoutes;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;

final class RestrictAdminDomainToAdminRoutesTest extends TestCase
{
    private RestrictAdminDomainToAdminRoutes $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new RestrictAdminDomainToAdminRoutes();
    }

    private function makeRequest(string $host, string $path, bool $json = false): Request
    {
        $request = Request::create('http://' . $host . '/' . ltrim($path, '/'));
        if ($json) {
            $request->headers->set('Accept', 'application/json');
        }

        return $request;
    }

    // --- No admin domain configured ---

    public function test_passes_through_when_no_admin_domain_configured(): void
    {
        config(['admin.route.domain' => null]);

        $request  = $this->makeRequest('example.com', '/about');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    // --- Non-admin domain ---

    public function test_passes_through_for_non_admin_domain(): void
    {
        config(['admin.route.domain' => 'admin.example.com']);

        $request  = $this->makeRequest('www.example.com', '/about');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Admin domain with admin path ---

    public function test_allows_admin_path_on_admin_domain(): void
    {
        config(['admin.route.domain' => 'admin.example.com', 'admin.route.prefix' => 'admin']);

        $request  = $this->makeRequest('admin.example.com', '/admin');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_allows_admin_subpath_on_admin_domain(): void
    {
        config(['admin.route.domain' => 'admin.example.com', 'admin.route.prefix' => 'admin']);

        $request  = $this->makeRequest('admin.example.com', '/admin/users');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }

    // --- Admin domain with non-admin path ---

    public function test_returns_404_for_non_admin_path_on_admin_domain(): void
    {
        config(['admin.route.domain' => 'admin.example.com', 'admin.route.prefix' => 'admin']);

        $request  = $this->makeRequest('admin.example.com', '/about');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_returns_json_404_for_json_request_on_admin_domain(): void
    {
        config(['admin.route.domain' => 'admin.example.com', 'admin.route.prefix' => 'admin']);

        $request  = $this->makeRequest('admin.example.com', '/api/data', json: true);
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(404, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        $this->assertFalse($json['status']);
        $this->assertSame(404, $json['code']);
        $this->assertSame('Route not found', $json['message']);
    }

    // --- Edge: no admin prefix ---

    public function test_passes_through_when_admin_prefix_is_empty(): void
    {
        config(['admin.route.domain' => 'admin.example.com', 'admin.route.prefix' => '']);

        $request  = $this->makeRequest('admin.example.com', '/about');
        $response = $this->middleware->handle($request, fn ($r) => response('OK'));

        $this->assertSame(200, $response->getStatusCode());
    }
}
