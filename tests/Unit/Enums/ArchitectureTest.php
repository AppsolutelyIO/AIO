<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\Architecture;
use Appsolutely\AIO\Tests\TestCase;

final class ArchitectureTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = Architecture::cases();

        $this->assertCount(6, $cases);
        $this->assertSame('x86_64', Architecture::X86_64->value);
        $this->assertSame('arm64', Architecture::ARM64->value);
        $this->assertSame('armv7', Architecture::ARMv7->value);
        $this->assertSame('ia32', Architecture::IA32->value);
        $this->assertSame('universal', Architecture::Universal->value);
        $this->assertSame('other', Architecture::Other->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('x86_64 (64-bit Intel/AMD)', Architecture::X86_64->label());
        $this->assertSame('arm64 (Apple Silicon, ARM64)', Architecture::ARM64->label());
        $this->assertSame('Other', Architecture::Other->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = Architecture::toArray();

        $this->assertCount(6, $array);
        $this->assertArrayHasKey('x86_64', $array);
        $this->assertArrayHasKey('arm64', $array);
        $this->assertArrayHasKey('other', $array);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(Architecture::X86_64, Architecture::from('x86_64'));
        $this->assertSame(Architecture::ARM64, Architecture::from('arm64'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(Architecture::tryFrom('invalid'));
    }
}
