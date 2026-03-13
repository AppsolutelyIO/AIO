<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\BuildStatus;
use Appsolutely\AIO\Tests\TestCase;

final class BuildStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = BuildStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertSame('pending', BuildStatus::Pending->value);
        $this->assertSame('building', BuildStatus::Building->value);
        $this->assertSame('success', BuildStatus::Success->value);
        $this->assertSame('failed', BuildStatus::Failed->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Pending', BuildStatus::Pending->label());
        $this->assertSame('Building', BuildStatus::Building->label());
        $this->assertSame('Success', BuildStatus::Success->label());
        $this->assertSame('Failed', BuildStatus::Failed->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = BuildStatus::toArray();

        $this->assertCount(4, $array);
        $this->assertSame('Pending', $array['pending']);
        $this->assertSame('Success', $array['success']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(BuildStatus::Pending, BuildStatus::from('pending'));
        $this->assertSame(BuildStatus::Failed, BuildStatus::from('failed'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(BuildStatus::tryFrom('unknown'));
    }
}
