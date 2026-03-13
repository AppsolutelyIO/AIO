<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Appsolutely\AIO\Tests\TestCase;

final class TrustProxiesTest extends TestCase
{
    // --- Trusts all proxies ---

    public function test_trusts_all_proxies(): void
    {
        $middleware = new TrustProxies();

        $reflection = new \ReflectionClass($middleware);
        $proxies    = $reflection->getProperty('proxies');

        $this->assertSame('*', $proxies->getValue($middleware));
    }

    // --- Trusted headers ---

    public function test_trusts_x_forwarded_for_header(): void
    {
        $middleware = new TrustProxies();

        $reflection = new \ReflectionClass($middleware);
        $headers    = $reflection->getProperty('headers');

        $this->assertTrue(($headers->getValue($middleware) & Request::HEADER_X_FORWARDED_FOR) !== 0);
    }

    public function test_trusts_x_forwarded_host_header(): void
    {
        $middleware = new TrustProxies();

        $reflection = new \ReflectionClass($middleware);
        $headers    = $reflection->getProperty('headers');

        $this->assertTrue(($headers->getValue($middleware) & Request::HEADER_X_FORWARDED_HOST) !== 0);
    }

    public function test_trusts_x_forwarded_port_header(): void
    {
        $middleware = new TrustProxies();

        $reflection = new \ReflectionClass($middleware);
        $headers    = $reflection->getProperty('headers');

        $this->assertTrue(($headers->getValue($middleware) & Request::HEADER_X_FORWARDED_PORT) !== 0);
    }

    public function test_trusts_x_forwarded_proto_header(): void
    {
        $middleware = new TrustProxies();

        $reflection = new \ReflectionClass($middleware);
        $headers    = $reflection->getProperty('headers');

        $this->assertTrue(($headers->getValue($middleware) & Request::HEADER_X_FORWARDED_PROTO) !== 0);
    }

    public function test_trusts_aws_elb_header(): void
    {
        $middleware = new TrustProxies();

        $reflection = new \ReflectionClass($middleware);
        $headers    = $reflection->getProperty('headers');

        $this->assertTrue(($headers->getValue($middleware) & Request::HEADER_X_FORWARDED_AWS_ELB) !== 0);
    }

    // --- Extends base middleware ---

    public function test_extends_laravel_trust_proxies_middleware(): void
    {
        $middleware = new TrustProxies();

        $this->assertInstanceOf(\Illuminate\Http\Middleware\TrustProxies::class, $middleware);
    }
}
