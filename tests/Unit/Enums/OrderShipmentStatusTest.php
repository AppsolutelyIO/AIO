<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Tests\TestCase;

final class OrderShipmentStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = OrderShipmentStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertSame('pending', OrderShipmentStatus::Pending->value);
        $this->assertSame('shipped', OrderShipmentStatus::Shipped->value);
        $this->assertSame('delivered', OrderShipmentStatus::Delivered->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Pending', OrderShipmentStatus::Pending->label());
        $this->assertSame('Shipped', OrderShipmentStatus::Shipped->label());
        $this->assertSame('Delivered', OrderShipmentStatus::Delivered->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = OrderShipmentStatus::toArray();

        $this->assertCount(3, $array);
        $this->assertSame('Pending', $array['pending']);
        $this->assertSame('Shipped', $array['shipped']);
        $this->assertSame('Delivered', $array['delivered']);
    }
}
