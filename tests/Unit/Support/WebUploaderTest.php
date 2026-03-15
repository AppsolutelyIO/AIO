<?php

namespace Appsolutely\AIO\Tests\Unit\Support;

use Appsolutely\AIO\Support\WebUploader;
use Appsolutely\AIO\Tests\Unit\TestCase;

class WebUploaderTest extends TestCase
{
    // --- putTempFileContent parameter name ---
    // Verify the method signature has correctly named parameters

    public function test_put_temp_file_content_has_correct_parameter_names()
    {
        $ref    = new \ReflectionMethod(WebUploader::class, 'putTempFileContent');
        $params = $ref->getParameters();

        $this->assertSame('path', $params[0]->getName());
        $this->assertSame('tmpDir', $params[1]->getName());
        // After fix: should be 'newFilename' not 'newFileame'
        $this->assertSame('newFilename', $params[2]->getName());
    }
}
