<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Middleware;

use Appsolutely\AIO\Http\Middleware\ThrottleFormSubmissions;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Mockery;
use Mockery\MockInterface;

final class ThrottleFormSubmissionsTest extends TestCase
{
    private ThrottleRequests|MockInterface $throttleRequests;

    private ThrottleFormSubmissions $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->throttleRequests = Mockery::mock(ThrottleRequests::class);
        $this->middleware       = new ThrottleFormSubmissions($this->throttleRequests);
    }

    // --- Delegates to ThrottleRequests ---

    public function test_delegates_to_throttle_requests_with_form_submission_limiter(): void
    {
        $request = Request::create('http://example.com/form/submit', 'POST');
        $next    = fn ($r) => response('OK');

        $this->throttleRequests->shouldReceive('handle')
            ->once()
            ->with($request, $next, 'form-submission')
            ->andReturn(response('OK'));

        $response = $this->middleware->handle($request, $next);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_returns_response_from_throttle_requests(): void
    {
        $request          = Request::create('http://example.com/form/submit', 'POST');
        $next             = fn ($r) => response('OK');
        $expectedResponse = response('Throttled', 429);

        $this->throttleRequests->shouldReceive('handle')
            ->once()
            ->andReturn($expectedResponse);

        $response = $this->middleware->handle($request, $next);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('Throttled', $response->getContent());
    }

    public function test_passes_correct_rate_limiter_name(): void
    {
        $request         = Request::create('http://example.com/contact', 'POST');
        $next            = fn ($r) => response('OK');
        $capturedLimiter = null;

        $this->throttleRequests->shouldReceive('handle')
            ->once()
            ->withArgs(function ($req, $nxt, $limiter) use (&$capturedLimiter) {
                $capturedLimiter = $limiter;

                return true;
            })
            ->andReturn(response('OK'));

        $this->middleware->handle($request, $next);

        $this->assertSame('form-submission', $capturedLimiter);
    }
}
