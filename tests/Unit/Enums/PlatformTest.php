<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\Platform;
use Appsolutely\AIO\Tests\TestCase;

final class PlatformTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = Platform::cases();

        $this->assertCount(6, $cases);
        $this->assertSame('windows', Platform::Windows->value);
        $this->assertSame('darwin', Platform::Darwin->value);
        $this->assertSame('linux', Platform::Linux->value);
        $this->assertSame('ios', Platform::iOS->value);
        $this->assertSame('android', Platform::Android->value);
        $this->assertSame('other', Platform::Other->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Windows', Platform::Windows->label());
        $this->assertSame('Darwin', Platform::Darwin->label());
        $this->assertSame('Linux', Platform::Linux->label());
        $this->assertSame('iOS', Platform::iOS->label());
        $this->assertSame('Android', Platform::Android->label());
        $this->assertSame('Other', Platform::Other->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = Platform::toArray();

        $this->assertCount(6, $array);
        $this->assertArrayHasKey('windows', $array);
        $this->assertArrayHasKey('ios', $array);
        $this->assertSame('Linux', $array['linux']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(Platform::Linux, Platform::from('linux'));
        $this->assertSame(Platform::iOS, Platform::from('ios'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(Platform::tryFrom('invalid'));
    }
}
