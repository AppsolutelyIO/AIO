<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\NotificationTriggerType;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationTriggerTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = NotificationTriggerType::cases();

        $this->assertCount(3, $cases);
        $this->assertSame('form_submission', NotificationTriggerType::FormSubmission->value);
        $this->assertSame('user_registration', NotificationTriggerType::UserRegistration->value);
        $this->assertSame('order_placed', NotificationTriggerType::OrderPlaced->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Form submission', NotificationTriggerType::FormSubmission->label());
        $this->assertSame('User registration', NotificationTriggerType::UserRegistration->label());
        $this->assertSame('Order placed', NotificationTriggerType::OrderPlaced->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = NotificationTriggerType::toArray();

        $this->assertCount(3, $array);
        $this->assertSame('Form submission', $array['form_submission']);
        $this->assertSame('User registration', $array['user_registration']);
        $this->assertSame('Order placed', $array['order_placed']);
    }

    public function test_can_construct_from_string_value(): void
    {
        $this->assertSame(NotificationTriggerType::FormSubmission, NotificationTriggerType::from('form_submission'));
    }

    public function test_try_from_returns_null_for_invalid_value(): void
    {
        $this->assertNull(NotificationTriggerType::tryFrom('invalid'));
    }
}
