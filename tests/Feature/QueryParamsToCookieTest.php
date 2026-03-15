<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Http\Middleware\QueryParamsToCookie;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QueryParamsToCookieTest extends TestCase
{
    private function handleMiddleware(string $queryString = ''): Response
    {
        $request = Request::create('/' . ($queryString ? '?' . $queryString : ''));

        $middleware = new QueryParamsToCookie();

        return $middleware->handle($request, fn () => new Response('OK'));
    }

    public function test_query_params_are_stored_as_cookies(): void
    {
        $response = $this->handleMiddleware('utm_source=google&utm_medium=cpc&gclid=abc123');

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertEquals('google', $cookies->get('utm_source')?->getValue());
        $this->assertEquals('cpc', $cookies->get('utm_medium')?->getValue());
        $this->assertEquals('abc123', $cookies->get('gclid')?->getValue());
    }

    public function test_vehicle_interest_query_param_is_stored_as_cookie(): void
    {
        $response = $this->handleMiddleware('vehicle_interest=AION-V');

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertEquals('AION-V', $cookies->get('vehicle_interest')?->getValue());
    }

    public function test_missing_query_params_do_not_create_cookies(): void
    {
        $response = $this->handleMiddleware();

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertNull($cookies->get('utm_source'));
        $this->assertNull($cookies->get('gclid'));
        $this->assertNull($cookies->get('vehicle_interest'));
    }

    public function test_empty_query_params_do_not_create_cookies(): void
    {
        $response = $this->handleMiddleware('utm_source=&gclid=');

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertNull($cookies->get('utm_source'));
        $this->assertNull($cookies->get('gclid'));
    }

    public function test_unconfigured_query_params_are_ignored(): void
    {
        $response = $this->handleMiddleware('random_param=value');

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertNull($cookies->get('random_param'));
    }

    public function test_overwrite_disabled_preserves_existing_cookie(): void
    {
        config(['query_params_cookie.parameters.utm_source.overwrite' => false]);

        $request = Request::create('/?utm_source=bing');
        $request->cookies->set('utm_source', 'google');

        $middleware = new QueryParamsToCookie();
        $response   = $middleware->handle($request, fn () => new Response('OK'));

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        // Should not set a new cookie when overwrite is disabled and cookie exists
        $this->assertNull($cookies->get('utm_source'));
    }

    public function test_overwrite_enabled_replaces_existing_cookie(): void
    {
        config(['query_params_cookie.parameters.utm_source.overwrite' => true]);

        $request = Request::create('/?utm_source=bing');
        $request->cookies->set('utm_source', 'google');

        $middleware = new QueryParamsToCookie();
        $response   = $middleware->handle($request, fn () => new Response('OK'));

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertEquals('bing', $cookies->get('utm_source')?->getValue());
    }

    public function test_custom_lifetime_is_applied(): void
    {
        config(['query_params_cookie.parameters.gclid.lifetime' => 60]);

        $response = $this->handleMiddleware('gclid=test123');

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertEquals('test123', $cookies->get('gclid')?->getValue());
    }

    public function test_multiple_params_from_real_url(): void
    {
        $response = $this->handleMiddleware(
            'vehicle_interest=AION-V&gclid=IwT01FWAQHWg5leHRuA2FlbQIxMAB&utm_source=google&utm_medium=cpc'
        );

        $cookies = collect($response->headers->getCookies())->keyBy(fn ($c) => $c->getName());

        $this->assertEquals('AION-V', $cookies->get('vehicle_interest')?->getValue());
        $this->assertEquals('IwT01FWAQHWg5leHRuA2FlbQIxMAB', $cookies->get('gclid')?->getValue());
        $this->assertEquals('google', $cookies->get('utm_source')?->getValue());
        $this->assertEquals('cpc', $cookies->get('utm_medium')?->getValue());
    }
}
