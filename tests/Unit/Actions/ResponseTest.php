<?php

namespace Appsolutely\AIO\Tests\Unit\Actions;

use Appsolutely\AIO\Http\JsonResponse;
use Appsolutely\AIO\Tests\Unit\TestCase;

class ResponseTest extends TestCase
{
    protected function makeResponse(): JsonResponse
    {
        return new JsonResponse();
    }

    // --- Basic status ---

    public function test_default_status_is_true()
    {
        $response = $this->makeResponse();
        $array = $response->toArray();
        $this->assertTrue($array['status']);
    }

    public function test_success_sets_status_true_and_type()
    {
        $response = $this->makeResponse()->success('Done!');
        $array = $response->toArray();

        $this->assertTrue($array['status']);
        $this->assertSame('success', $array['data']['type']);
        $this->assertSame('Done!', $array['data']['message']);
    }

    public function test_error_sets_status_false_and_type()
    {
        $response = $this->makeResponse()->error('Failed!');
        $array = $response->toArray();

        $this->assertFalse($array['status']);
        $this->assertSame('error', $array['data']['type']);
        $this->assertSame('Failed!', $array['data']['message']);
    }

    public function test_info_sets_type()
    {
        $response = $this->makeResponse()->info('Notice');
        $array = $response->toArray();

        $this->assertSame('info', $array['data']['type']);
        $this->assertSame('Notice', $array['data']['message']);
    }

    public function test_warning_sets_type()
    {
        $response = $this->makeResponse()->warning('Caution');
        $array = $response->toArray();

        $this->assertSame('warning', $array['data']['type']);
        $this->assertSame('Caution', $array['data']['message']);
    }

    // --- Actions ---

    public function test_refresh_action()
    {
        $response = $this->makeResponse()->refresh();
        $array = $response->toArray();

        $this->assertSame('refresh', $array['data']['then']['action']);
        $this->assertTrue($array['data']['then']['value']);
    }

    public function test_script_action()
    {
        $response = $this->makeResponse()->script('alert(1)');
        $array = $response->toArray();

        $this->assertSame('script', $array['data']['then']['action']);
        $this->assertSame('alert(1)', $array['data']['then']['value']);
    }

    // --- Data ---

    public function test_data_merges()
    {
        $response = $this->makeResponse()
            ->data(['foo' => 1])
            ->data(['bar' => 2]);
        $array = $response->toArray();

        $this->assertSame(1, $array['data']['foo']);
        $this->assertSame(2, $array['data']['bar']);
    }

    public function test_message()
    {
        $response = $this->makeResponse()->message('test');
        $array = $response->toArray();

        $this->assertSame('test', $array['data']['message']);
    }

    public function test_detail()
    {
        $response = $this->makeResponse()->detail('Some detail');
        $array = $response->toArray();

        $this->assertSame('Some detail', $array['data']['detail']);
    }

    public function test_alert()
    {
        $response = $this->makeResponse()->alert();
        $array = $response->toArray();

        $this->assertTrue($array['data']['alert']);
    }

    public function test_timeout()
    {
        $response = $this->makeResponse()->timeout(5);
        $array = $response->toArray();

        $this->assertSame(5, $array['data']['timeout']);
    }

    // --- HTML ---

    public function test_html()
    {
        $response = $this->makeResponse()->html('<div>test</div>');
        $array = $response->toArray();

        $this->assertSame('<div>test</div>', $array['html']);
    }

    // --- Options ---

    public function test_options_merges()
    {
        $response = $this->makeResponse()
            ->options(['key1' => 'val1'])
            ->options(['key2' => 'val2']);
        $array = $response->toArray();

        $this->assertSame('val1', $array['key1']);
        $this->assertSame('val2', $array['key2']);
    }

    // --- Status code ---

    public function test_status_code()
    {
        $response = $this->makeResponse()->statusCode(201);
        // statusCode is used in send(), we can verify through withValidation
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    // --- Validation ---

    public function test_with_validation_sets_errors()
    {
        $response = $this->makeResponse()->withValidation(['name' => ['Name is required']]);
        $array = $response->toArray();

        $this->assertFalse($array['status']);
        $this->assertSame(['name' => ['Name is required']], $array['errors']);
    }

    // --- Exception handling ---

    public function test_with_exception()
    {
        $exception = new \RuntimeException('Something broke');
        $response = $this->makeResponse()->withException($exception);
        $array = $response->toArray();

        $this->assertFalse($array['status']);
        $this->assertStringContainsString('RuntimeException', $array['data']['message']);
        $this->assertStringContainsString('Something broke', $array['data']['message']);
    }

    // --- Conditional methods ---

    public function test_success_if_true()
    {
        $response = $this->makeResponse()->successIf(true, 'Yay');
        $array = $response->toArray();

        $this->assertSame('success', $array['data']['type']);
    }

    public function test_success_if_false()
    {
        $response = $this->makeResponse()->successIf(false, 'Yay');
        $array = $response->toArray();

        $this->assertArrayNotHasKey('type', $array['data']);
    }

    public function test_error_if_true()
    {
        $response = $this->makeResponse()->errorIf(true, 'Oops');
        $array = $response->toArray();

        $this->assertSame('error', $array['data']['type']);
    }

    public function test_error_if_false_does_nothing()
    {
        $response = $this->makeResponse()->errorIf(false, 'Oops');
        $array = $response->toArray();

        $this->assertArrayNotHasKey('type', $array['data']);
    }

    // --- Make ---

    public function test_make_static_constructor()
    {
        $response = JsonResponse::make(['initial' => 'data']);
        $array = $response->toArray();

        $this->assertSame('data', $array['data']['initial']);
    }

    // --- Chaining ---

    public function test_fluent_chaining()
    {
        $response = $this->makeResponse()
            ->success('OK')
            ->detail('Details here')
            ->alert()
            ->timeout(3)
            ->data(['extra' => 'stuff']);

        $array = $response->toArray();

        $this->assertTrue($array['status']);
        $this->assertSame('success', $array['data']['type']);
        $this->assertSame('OK', $array['data']['message']);
        $this->assertSame('Details here', $array['data']['detail']);
        $this->assertTrue($array['data']['alert']);
        $this->assertSame(3, $array['data']['timeout']);
        $this->assertSame('stuff', $array['data']['extra']);
    }
}
