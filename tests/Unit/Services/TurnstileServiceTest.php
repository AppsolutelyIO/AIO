<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\TurnstileService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;

final class TurnstileServiceTest extends TestCase
{
    private TurnstileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TurnstileService(app(LoggerInterface::class));
    }

    // --- isEnabled ---

    public function test_is_disabled_by_default(): void
    {
        $this->assertFalse($this->service->isEnabled());
    }

    public function test_is_enabled_when_config_is_set(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        $this->assertTrue($this->service->isEnabled());
    }

    public function test_is_disabled_when_secret_key_is_empty(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => '',
        ]);

        $this->assertFalse($this->service->isEnabled());
    }

    // --- verify ---

    public function test_verify_returns_true_when_disabled(): void
    {
        config(['forms.captcha.turnstile.enabled' => false]);

        $this->assertTrue($this->service->verify('any-token'));
    }

    public function test_verify_returns_false_for_empty_token(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        $this->assertFalse($this->service->verify(''));
    }

    public function test_verify_returns_true_on_successful_verification(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $this->assertTrue($this->service->verify('valid-token', '127.0.0.1'));

        Http::assertSent(function ($request) {
            return $request['secret']   === 'test-secret'
                && $request['response'] === 'valid-token'
                && $request['remoteip'] === '127.0.0.1';
        });
    }

    public function test_verify_returns_false_on_failed_verification(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response([
                'success'     => false,
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);

        $this->assertFalse($this->service->verify('invalid-token'));
    }

    public function test_verify_returns_false_on_http_error(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(null, 500),
        ]);

        $this->assertFalse($this->service->verify('some-token'));
    }

    public function test_verify_fails_open_on_network_exception(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => fn () => throw new \RuntimeException('Network error'),
        ]);

        // Should fail open — return true on network errors
        $this->assertTrue($this->service->verify('some-token'));
    }

    public function test_verify_omits_remoteip_when_null(): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.secret_key' => 'test-secret',
        ]);

        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $this->service->verify('valid-token');

        Http::assertSent(function ($request) {
            return ! isset($request['remoteip']);
        });
    }
}
