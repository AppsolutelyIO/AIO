<?php

namespace Appsolutely\AIO\Tests\Unit\Traits;

use Appsolutely\AIO\Tests\Unit\TestCase;
use Appsolutely\AIO\Traits\InteractsWithApi;

class InteractsWithApiTest extends TestCase
{
    private function createInstance()
    {
        return new class {
            use InteractsWithApi;

            // Expose the url property for testing
            public function setUrl(?string $url): void
            {
                $this->url = $url;
            }
        };
    }

    // --- InteractsWithApi::allowBuildRequest() ---

    public function test_allow_build_request_returns_false_by_default()
    {
        $instance = $this->createInstance();
        $this->assertFalse($instance->allowBuildRequest());
    }

    public function test_allow_build_request_returns_bool_type()
    {
        $instance = $this->createInstance();
        $this->assertIsBool($instance->allowBuildRequest());
    }

    public function test_allow_build_request_returns_true_when_url_set()
    {
        $instance = $this->createInstance();
        $instance->setUrl('/api/test');
        $this->assertTrue($instance->allowBuildRequest());
    }

    public function test_allow_build_request_returns_true_when_handle_method_exists()
    {
        $instance = new class {
            use InteractsWithApi;

            public function handle()
            {
                return 'handled';
            }
        };

        $this->assertTrue($instance->allowBuildRequest());
    }

    public function test_allow_build_request_returns_bool_type_when_truthy()
    {
        $instance = $this->createInstance();
        $instance->setUrl('/api/test');
        $this->assertIsBool($instance->allowBuildRequest());
    }
}
