<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\OrderPaymentStatus;
use Appsolutely\AIO\Tests\TestCase;

final class OrderPaymentStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = OrderPaymentStatus::cases();

        $this->assertCount(4, $cases);
        $this->assertSame('pending', OrderPaymentStatus::Pending->value);
        $this->assertSame('paid', OrderPaymentStatus::Paid->value);
        $this->assertSame('failed', OrderPaymentStatus::Failed->value);
        $this->assertSame('refunded', OrderPaymentStatus::Refunded->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Pending', OrderPaymentStatus::Pending->label());
        $this->assertSame('Paid', OrderPaymentStatus::Paid->label());
        $this->assertSame('Failed', OrderPaymentStatus::Failed->label());
        $this->assertSame('Refunded', OrderPaymentStatus::Refunded->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = OrderPaymentStatus::toArray();

        $this->assertCount(4, $array);
        $this->assertSame('Pending', $array['pending']);
        $this->assertSame('Paid', $array['paid']);
        $this->assertSame('Failed', $array['failed']);
        $this->assertSame('Refunded', $array['refunded']);
    }
}
