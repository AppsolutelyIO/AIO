<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Http\Middleware\StagingAccessGate;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StagingAccessGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'aio.staging_access_enabled' => true,
            'app.url'                    => 'https://staging.example.com',
        ]);
    }

    private function token(): string
    {
        return StagingAccessGate::generateToken();
    }

    private function handleMiddleware(Request $request): Response|RedirectResponse
    {
        $middleware = new StagingAccessGate();

        return $middleware->handle($request, fn () => new Response('OK'));
    }

    private function makeRequest(string $uri = '/', array $cookies = []): Request
    {
        $request = Request::create($uri);

        foreach ($cookies as $name => $value) {
            $request->cookies->set($name, $value);
        }

        return $request;
    }

    public function test_returns_404_without_token(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->handleMiddleware($this->makeRequest());
    }

    public function test_returns_404_with_wrong_token(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->handleMiddleware($this->makeRequest('/?token=wrong-token'));
    }

    public function test_redirects_and_sets_cookie_with_valid_token(): void
    {
        $response = $this->handleMiddleware($this->makeRequest('/?token=' . $this->token()));

        $this->assertTrue($response->isRedirection());
        $this->assertStringNotContainsString('token=', $response->getTargetUrl());

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());
        $this->assertNotNull($cookies->get('staging_access'));
        $this->assertEquals(hash('sha256', $this->token()), $cookies->get('staging_access')->getValue());
    }

    public function test_allows_access_with_valid_cookie(): void
    {
        $response = $this->handleMiddleware($this->makeRequest('/', [
            'staging_access' => hash('sha256', $this->token()),
        ]));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_returns_404_with_invalid_cookie(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->handleMiddleware($this->makeRequest('/', [
            'staging_access' => 'invalid-hash',
        ]));
    }

    public function test_middleware_inactive_when_disabled(): void
    {
        config(['aio.staging_access_enabled' => false]);

        $response = $this->handleMiddleware($this->makeRequest());

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_preserves_other_query_params(): void
    {
        $response = $this->handleMiddleware(
            $this->makeRequest('/?token=' . $this->token() . '&page=2&sort=name')
        );

        $this->assertTrue($response->isRedirection());
        $this->assertStringContainsString('page=2', $response->getTargetUrl());
        $this->assertStringContainsString('sort=name', $response->getTargetUrl());
        $this->assertStringNotContainsString('token=', $response->getTargetUrl());
    }

    public function test_different_urls_produce_different_tokens(): void
    {
        $token1 = StagingAccessGate::generateToken('https://pr-1.staging.example.com');
        $token2 = StagingAccessGate::generateToken('https://pr-2.staging.example.com');

        $this->assertNotEquals($token1, $token2);
    }

    public function test_token_is_deterministic(): void
    {
        $this->assertEquals($this->token(), $this->token());
    }
}
