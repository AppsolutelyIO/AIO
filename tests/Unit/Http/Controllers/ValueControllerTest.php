<?php

namespace Appsolutely\AIO\Tests\Unit\Http\Controllers;

use Appsolutely\AIO\Http\Controllers\ValueController;
use Appsolutely\AIO\Tests\Unit\TestCase;
use Exception;
use Illuminate\Http\Request;

class ValueControllerTest extends TestCase
{
    protected function callResolve(Request $request): object
    {
        $controller = new ValueController();
        $method = new \ReflectionMethod($controller, 'resolve');

        return $method->invoke($controller, $request);
    }

    public function test_resolve_throws_when_key_missing()
    {
        $request = Request::create('/test', 'GET');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid request');

        $this->callResolve($request);
    }

    public function test_resolve_throws_when_class_does_not_exist()
    {
        $request = Request::create('/test', 'GET', ['_key' => 'NonExistentClass123']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('does not exist');

        $this->callResolve($request);
    }

    public function test_resolve_throws_when_class_lacks_handle_method()
    {
        $request = Request::create('/test', 'GET', ['_key' => \stdClass::class]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('handle()');

        $this->callResolve($request);
    }

    public function test_resolve_throws_when_class_lacks_authorization()
    {
        // A class with handle() but without HasAuthorization
        $request = Request::create('/test', 'GET', [
            '_key' => ValueControllerTestClassWithHandleOnly::class,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('HasAuthorization');

        $this->callResolve($request);
    }

    public function test_resolve_returns_instance_with_authorization_and_handle()
    {
        $request = Request::create('/test', 'GET', [
            '_key' => ValueControllerTestValidClass::class,
        ]);

        $instance = $this->callResolve($request);
        $this->assertInstanceOf(ValueControllerTestValidClass::class, $instance);
    }
}

/**
 * Test double: has handle() but no passesAuthorization().
 */
class ValueControllerTestClassWithHandleOnly
{
    public function handle(Request $request)
    {
        return null;
    }
}

/**
 * Test double: has both handle() and passesAuthorization().
 */
class ValueControllerTestValidClass
{
    use \Appsolutely\AIO\Traits\HasAuthorization;

    public function handle(Request $request)
    {
        return null;
    }
}
