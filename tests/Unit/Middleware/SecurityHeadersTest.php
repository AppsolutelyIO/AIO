<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\SecurityHeaders;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecurityHeadersTest extends TestCase
{
    private SecurityHeaders $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeaders();
    }

    private function makeRequest(bool $secure = false): Request
    {
        $scheme = $secure ? 'https' : 'http';

        return Request::create("{$scheme}://example.com/test");
    }

    private function handle(Request $request): Response
    {
        return $this->middleware->handle($request, fn ($r) => response('OK'));
    }

    // --- X-Frame-Options ---

    public function test_sets_x_frame_options_header(): void
    {
        $response = $this->handle($this->makeRequest());

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    // --- X-Content-Type-Options ---

    public function test_sets_x_content_type_options_header(): void
    {
        $response = $this->handle($this->makeRequest());

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    // --- X-XSS-Protection ---

    public function test_sets_xss_protection_header(): void
    {
        $response = $this->handle($this->makeRequest());

        $this->assertSame('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    // --- Referrer-Policy ---

    public function test_sets_referrer_policy_header(): void
    {
        $response = $this->handle($this->makeRequest());

        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    // --- Permissions-Policy ---

    public function test_sets_permissions_policy_header(): void
    {
        $response = $this->handle($this->makeRequest());

        $permissionsPolicy = $response->headers->get('Permissions-Policy');
        $this->assertNotNull($permissionsPolicy);
        $this->assertStringContainsString('geolocation=()', $permissionsPolicy);
        $this->assertStringContainsString('microphone=()', $permissionsPolicy);
        $this->assertStringContainsString('camera=()', $permissionsPolicy);
        $this->assertStringContainsString('payment=()', $permissionsPolicy);
    }

    // --- Content-Security-Policy ---

    public function test_sets_csp_header_in_non_production(): void
    {
        $response = $this->handle($this->makeRequest());

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        // Development CSP allows ws: and wss: for hot reload
        $this->assertStringContainsString('ws:', $csp);
    }

    public function test_csp_includes_media_src_directive(): void
    {
        $response = $this->handle($this->makeRequest());

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('media-src', $csp);
    }

    public function test_uses_custom_csp_from_config_when_set(): void
    {
        config(['appsolutely.security.csp' => "default-src 'none'"]);

        $response = $this->handle($this->makeRequest());

        $this->assertSame("default-src 'none'", $response->headers->get('Content-Security-Policy'));
    }

    // --- HSTS ---

    public function test_does_not_add_hsts_in_non_production(): void
    {
        $response = $this->handle($this->makeRequest(secure: true));

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    // --- Response passthrough ---

    public function test_passes_response_through_unchanged(): void
    {
        $response = $this->handle($this->makeRequest());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getContent());
    }

    // --- Does not overwrite existing headers ---

    public function test_does_not_overwrite_existing_headers(): void
    {
        $response = $this->middleware->handle(
            $this->makeRequest(),
            fn ($r) => response('OK')->header('X-Frame-Options', 'DENY')
        );

        // The `false` parameter in headers->set() means it should NOT replace
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
    }
}
