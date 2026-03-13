<?php

namespace Appsolutely\AIO\Tests\Unit\Http\Controllers;

use Appsolutely\AIO\Http\Controllers\HandleFormController;
use Appsolutely\AIO\Tests\Unit\TestCase;

class HandleFormControllerTest extends TestCase
{
    // --- getField() never returns null ---

    public function test_get_field_method_has_no_nullable_return_path()
    {
        $ref = new \ReflectionMethod(HandleFormController::class, 'getField');
        $source = file_get_contents($ref->getFileName());
        $startLine = $ref->getStartLine();
        $endLine = $ref->getEndLine();

        $lines = array_slice(explode("\n", $source), $startLine - 1, $endLine - $startLine + 1);
        $methodBody = implode("\n", $lines);

        // After fix: method should never have a bare "return;" (null return)
        $this->assertStringNotContainsString(
            'return;',
            $methodBody,
            'getField() should not have bare return statements that return null'
        );
    }
}
