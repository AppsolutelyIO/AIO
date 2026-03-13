<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\NotificationRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationRuleTest extends TestCase
{
    use RefreshDatabase;

    // --- getRecipientEmailsListAttribute ---

    public function test_get_recipient_emails_list_returns_array(): void
    {
        $rule = NotificationRule::factory()->create([
            'recipient_emails' => ['alice@example.com', 'bob@example.com'],
        ]);

        $result = $rule->recipient_emails_list;

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContains('alice@example.com', $result);
    }

    public function test_get_recipient_emails_list_returns_empty_array_for_null(): void
    {
        $rule                   = NotificationRule::factory()->make();
        $rule->recipient_emails = null;

        $result = $rule->recipient_emails_list;

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- getHasConditionsAttribute ---

    public function test_get_has_conditions_returns_true_when_conditions_set(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'email', 'operator' => 'equals', 'value' => 'test@test.com'],
        ]);

        $this->assertTrue($rule->has_conditions);
    }

    public function test_get_has_conditions_returns_false_when_conditions_null(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => null,
        ]);

        $this->assertFalse($rule->has_conditions);
    }

    public function test_get_has_conditions_returns_false_when_conditions_empty_array(): void
    {
        $rule             = NotificationRule::factory()->make();
        $rule->conditions = [];

        $this->assertFalse($rule->has_conditions);
    }

    // --- getScheduledAt ---

    public function test_get_scheduled_at_returns_now_for_zero_delay(): void
    {
        $rule = NotificationRule::factory()->make(['delay_minutes' => 0]);

        $scheduledAt = $rule->getScheduledAt();

        $this->assertEqualsWithDelta(now()->timestamp, $scheduledAt->timestamp, 2);
    }

    public function test_get_scheduled_at_adds_delay_minutes(): void
    {
        $rule = NotificationRule::factory()->make(['delay_minutes' => 60]);

        $scheduledAt = $rule->getScheduledAt();

        $this->assertEqualsWithDelta(now()->addMinutes(60)->timestamp, $scheduledAt->timestamp, 2);
    }

    // --- evaluateConditions ---

    public function test_evaluate_conditions_returns_true_with_no_conditions(): void
    {
        $rule = NotificationRule::factory()->make(['conditions' => null]);

        $this->assertTrue($rule->evaluateConditions(['email' => 'test@test.com']));
    }

    public function test_evaluate_conditions_equals_operator_matches(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'status', 'operator' => 'equals', 'value' => 'active'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['status' => 'active']));
        $this->assertFalse($rule->evaluateConditions(['status' => 'inactive']));
    }

    public function test_evaluate_conditions_not_equals_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'type', 'operator' => 'not_equals', 'value' => 'admin'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['type' => 'user']));
        $this->assertFalse($rule->evaluateConditions(['type' => 'admin']));
    }

    public function test_evaluate_conditions_contains_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'message', 'operator' => 'contains', 'value' => 'Premium'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['message' => 'I want Premium plan']));
        $this->assertFalse($rule->evaluateConditions(['message' => 'I want Basic plan']));
    }

    public function test_evaluate_conditions_starts_with_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'code', 'operator' => 'starts_with', 'value' => 'VIP'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['code' => 'VIP-001']));
        $this->assertFalse($rule->evaluateConditions(['code' => 'REG-001']));
    }

    public function test_evaluate_conditions_ends_with_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'email', 'operator' => 'ends_with', 'value' => '@company.com'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['email' => 'user@company.com']));
        $this->assertFalse($rule->evaluateConditions(['email' => 'user@gmail.com']));
    }

    public function test_evaluate_conditions_in_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'plan', 'operator' => 'in', 'value' => ['gold', 'platinum']],
        ]);

        $this->assertTrue($rule->evaluateConditions(['plan' => 'gold']));
        $this->assertTrue($rule->evaluateConditions(['plan' => 'platinum']));
        $this->assertFalse($rule->evaluateConditions(['plan' => 'silver']));
    }

    public function test_evaluate_conditions_greater_than_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'amount', 'operator' => 'greater_than', 'value' => '100'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['amount' => '150']));
        $this->assertFalse($rule->evaluateConditions(['amount' => '50']));
    }

    public function test_evaluate_conditions_less_than_operator(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'age', 'operator' => 'less_than', 'value' => '18'],
        ]);

        $this->assertTrue($rule->evaluateConditions(['age' => '16']));
        $this->assertFalse($rule->evaluateConditions(['age' => '20']));
    }

    public function test_evaluate_conditions_unknown_operator_returns_false(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'status', 'operator' => 'unknown_op', 'value' => 'active'],
        ]);

        $this->assertFalse($rule->evaluateConditions(['status' => 'active']));
    }

    public function test_evaluate_conditions_incomplete_conditions_returns_false(): void
    {
        $rule = NotificationRule::factory()->create([
            'conditions' => ['field' => 'status'], // missing operator and value
        ]);

        $this->assertFalse($rule->evaluateConditions(['status' => 'active']));
    }
}
