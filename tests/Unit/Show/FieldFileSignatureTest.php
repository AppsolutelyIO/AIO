<?php

namespace Appsolutely\AIO\Tests\Unit\Show;

use Appsolutely\AIO\Show\Field;
use Appsolutely\AIO\Tests\Unit\TestCase;

class FieldFileSignatureTest extends TestCase
{
    // --- Show\Field::file() parameter signature ---

    public function test_file_method_accepts_server_parameter()
    {
        $ref    = new \ReflectionMethod(Field::class, 'file');
        $params = $ref->getParameters();

        $this->assertSame('server', $params[0]->getName());
        $this->assertTrue($params[0]->isOptional());
        $this->assertSame('', $params[0]->getDefaultValue());
    }

    public function test_file_method_has_only_one_parameter()
    {
        $ref = new \ReflectionMethod(Field::class, 'file');
        // After fix: $download parameter should be removed
        $this->assertCount(1, $ref->getParameters());
    }
}
