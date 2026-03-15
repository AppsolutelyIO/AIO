<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\CacheProfile;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;

final class CacheProfileTest extends TestCase
{
    private CacheProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profile = new CacheProfile();
    }

    private function makeGetRequest(string $path = '/', array $headers = []): Request
    {
        $request = Request::create($path, 'GET');
        foreach ($headers as $key => $value) {
            $request->headers->set($key, $value);
        }

        return $request;
    }

    // --- Livewire exclusion ---

    public function test_excludes_livewire_requests(): void
    {
        $request = $this->makeGetRequest('/', ['X-Livewire' => 'true']);

        $this->assertFalse($this->profile->shouldCacheRequest($request));
    }

    // --- Admin domain exclusion ---

    public function test_excludes_admin_domain_requests(): void
    {
        config(['admin.route.domain' => 'admin.example.com']);

        $request = Request::create('http://admin.example.com/');

        $this->assertFalse($this->profile->shouldCacheRequest($request));
    }

    public function test_allows_non_admin_domain_requests(): void
    {
        config(['admin.route.domain' => 'admin.example.com']);

        $request = Request::create('http://www.example.com/');

        $this->assertTrue($this->profile->shouldCacheRequest($request));
    }

    // --- Admin prefix exclusion ---

    public function test_excludes_admin_prefix_routes(): void
    {
        config(['admin.route.prefix' => 'admin', 'admin.route.domain' => null]);

        $request = $this->makeGetRequest('/admin');

        $this->assertFalse($this->profile->shouldCacheRequest($request));
    }

    public function test_excludes_admin_sub_routes(): void
    {
        config(['admin.route.prefix' => 'admin', 'admin.route.domain' => null]);

        $request = $this->makeGetRequest('/admin/dashboard');

        $this->assertFalse($this->profile->shouldCacheRequest($request));
    }

    // --- POST request exclusion (via parent class) ---

    public function test_excludes_post_requests(): void
    {
        $request = Request::create('/', 'POST');

        $this->assertFalse($this->profile->shouldCacheRequest($request));
    }

    // --- Normal cacheable request ---

    public function test_allows_normal_get_request(): void
    {
        config(['admin.route.domain' => null, 'admin.route.prefix' => 'admin']);

        $request = $this->makeGetRequest('/about');

        $this->assertTrue($this->profile->shouldCacheRequest($request));
    }
}
