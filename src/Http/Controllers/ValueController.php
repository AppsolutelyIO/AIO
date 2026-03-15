<?php

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Traits\InteractsWithApi;
use Exception;
use Illuminate\Http\Request;

class ValueController
{
    /**
     * @return mixed
     */
    public function handle(Request $request)
    {
        $instance = $this->resolve($request);

        if (! $instance->passesAuthorization()) {
            return $instance->failedAuthorization();
        }

        $response = $instance->handle($request);

        if ($response) {
            return $response;
        }

        if (method_exists($instance, 'valueResult')) {
            return $instance->valueResult();
        }
    }

    /**
     * @return InteractsWithApi
     *
     * @throws Exception
     */
    protected function resolve(Request $request)
    {
        if (! $key = $request->get('_key')) {
            throw new Exception('Invalid request.');
        }

        if (! class_exists($key)) {
            throw new Exception("Class [{$key}] does not exist.");
        }

        $instance = app($key);

        if (! method_exists($instance, 'handle')) {
            throw new Exception("The method '{$key}::handle()' does not exist.");
        }

        if (! method_exists($instance, 'passesAuthorization')) {
            throw new Exception("Class [{$key}] must use the HasAuthorization trait.");
        }

        return $instance;
    }
}
