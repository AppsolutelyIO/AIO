<?php

namespace Appsolutely\AIO\Tests\Unit\Traits;

use Appsolutely\AIO\Tests\Unit\TestCase;

class HasPermissionsTest extends TestCase
{
    // --- HasPermissions::can() parameter name ---
    // Verify the method signature uses correct parameter name

    public function test_can_method_has_correct_parameter_names()
    {
        // Use reflection to find a class that uses the trait
        $ref = new \ReflectionMethod(
            \Appsolutely\AIO\Models\Administrator::class,
            'can'
        );
        $params = $ref->getParameters();

        $this->assertSame('ability', $params[0]->getName());
        // After fix: should be 'parameters' not 'paramters'
        $this->assertSame('parameters', $params[1]->getName());
    }
}
