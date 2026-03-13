<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Tests\TestCase;

final class ProductTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = ProductType::cases();

        $this->assertCount(3, $cases);
        $this->assertSame('PHYSICAL', ProductType::Physical->value);
        $this->assertSame('AUTO_VIRTUAL', ProductType::AutoVirtual->value);
        $this->assertSame('MANUAL_VIRTUAL', ProductType::ManualVirtual->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Physical product', ProductType::Physical->label());
        $this->assertSame('Auto-deliverable virtual product', ProductType::AutoVirtual->label());
        $this->assertSame('Manual-deliverable virtual product', ProductType::ManualVirtual->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = ProductType::toArray();

        $this->assertCount(3, $array);
        $this->assertArrayHasKey('PHYSICAL', $array);
        $this->assertArrayHasKey('AUTO_VIRTUAL', $array);
        $this->assertArrayHasKey('MANUAL_VIRTUAL', $array);
    }
}
