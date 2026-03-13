<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\General;
use Appsolutely\AIO\Tests\TestCase;

final class GeneralTest extends TestCase
{
    public function test_enum_exists_with_no_cases(): void
    {
        $reflection = new \ReflectionEnum(General::class);

        $this->assertTrue($reflection->isEnum());
        $this->assertEmpty($reflection->getCases());
    }
}
