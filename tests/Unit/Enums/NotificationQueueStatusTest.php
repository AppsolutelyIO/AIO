<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationQueueStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = NotificationQueueStatus::cases();

        $this->assertCount(5, $cases);
        $this->assertSame('pending', NotificationQueueStatus::Pending->value);
        $this->assertSame('processing', NotificationQueueStatus::Processing->value);
        $this->assertSame('sent', NotificationQueueStatus::Sent->value);
        $this->assertSame('failed', NotificationQueueStatus::Failed->value);
        $this->assertSame('cancelled', NotificationQueueStatus::Cancelled->value);
    }

    public function test_label_returns_translated_string(): void
    {
        $this->assertIsString(NotificationQueueStatus::Pending->label());
        $this->assertIsString(NotificationQueueStatus::Sent->label());
    }

    public function test_color_returns_bootstrap_class(): void
    {
        $this->assertSame('warning', NotificationQueueStatus::Pending->color());
        $this->assertSame('info', NotificationQueueStatus::Processing->color());
        $this->assertSame('success', NotificationQueueStatus::Sent->color());
        $this->assertSame('danger', NotificationQueueStatus::Failed->color());
        $this->assertSame('secondary', NotificationQueueStatus::Cancelled->color());
    }

    public function test_to_array_returns_keyed_labels(): void
    {
        $array = NotificationQueueStatus::toArray();

        $this->assertArrayHasKey('pending', $array);
        $this->assertArrayHasKey('sent', $array);
        $this->assertArrayHasKey('failed', $array);
        $this->assertCount(5, $array);
    }

    public function test_values_returns_string_values(): void
    {
        $values = NotificationQueueStatus::values();

        $this->assertContains('pending', $values);
        $this->assertContains('processing', $values);
        $this->assertContains('sent', $values);
        $this->assertContains('failed', $values);
        $this->assertContains('cancelled', $values);
    }
}
