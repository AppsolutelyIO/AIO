<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Tests\TestCase;

final class OrderStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = OrderStatus::cases();

        $this->assertCount(5, $cases);
        $this->assertSame('pending', OrderStatus::Pending->value);
        $this->assertSame('paid', OrderStatus::Paid->value);
        $this->assertSame('shipped', OrderStatus::Shipped->value);
        $this->assertSame('completed', OrderStatus::Completed->value);
        $this->assertSame('cancelled', OrderStatus::Cancelled->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Pending', OrderStatus::Pending->label());
        $this->assertSame('Paid', OrderStatus::Paid->label());
        $this->assertSame('Shipped', OrderStatus::Shipped->label());
        $this->assertSame('Completed', OrderStatus::Completed->label());
        $this->assertSame('Cancelled', OrderStatus::Cancelled->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = OrderStatus::toArray();

        $this->assertCount(5, $array);
        $this->assertSame('Pending', $array['pending']);
        $this->assertSame('Paid', $array['paid']);
        $this->assertSame('Shipped', $array['shipped']);
        $this->assertSame('Completed', $array['completed']);
        $this->assertSame('Cancelled', $array['cancelled']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $status = OrderStatus::from('pending');

        $this->assertSame(OrderStatus::Pending, $status);
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $status = OrderStatus::tryFrom('invalid');

        $this->assertNull($status);
    }
}
