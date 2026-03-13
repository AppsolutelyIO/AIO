<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Services\NotificationRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationRuleServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationRuleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationRuleService(
            app(NotificationRuleRepository::class)
        );
    }

    // --- getAvailableTriggerTypes ---

    public function test_get_available_trigger_types_returns_array(): void
    {
        $result = $this->service->getAvailableTriggerTypes();

        $this->assertIsArray($result);
    }

    public function test_get_available_trigger_types_is_not_empty(): void
    {
        $result = $this->service->getAvailableTriggerTypes();

        $this->assertNotEmpty($result);
    }

    public function test_get_available_trigger_types_contains_form_submission(): void
    {
        $result = $this->service->getAvailableTriggerTypes();

        $this->assertArrayHasKey('form_submission', $result);
    }

    public function test_get_available_trigger_types_contains_user_registration(): void
    {
        $result = $this->service->getAvailableTriggerTypes();

        $this->assertArrayHasKey('user_registration', $result);
    }

    // --- getConditionOperators ---

    public function test_get_condition_operators_returns_array(): void
    {
        $result = $this->service->getConditionOperators();

        $this->assertIsArray($result);
    }

    public function test_get_condition_operators_contains_all_operators(): void
    {
        $result = $this->service->getConditionOperators();

        $this->assertArrayHasKey('equals', $result);
        $this->assertArrayHasKey('not_equals', $result);
        $this->assertArrayHasKey('contains', $result);
        $this->assertArrayHasKey('starts_with', $result);
        $this->assertArrayHasKey('ends_with', $result);
        $this->assertArrayHasKey('in', $result);
        $this->assertArrayHasKey('greater_than', $result);
        $this->assertArrayHasKey('less_than', $result);
    }

    public function test_get_condition_operators_has_human_readable_values(): void
    {
        $result = $this->service->getConditionOperators();

        $this->assertEquals('Equals', $result['equals']);
        $this->assertEquals('Contains', $result['contains']);
    }

    // --- getRecipientTypes ---

    public function test_get_recipient_types_returns_array(): void
    {
        $result = $this->service->getRecipientTypes();

        $this->assertIsArray($result);
    }

    public function test_get_recipient_types_contains_expected_types(): void
    {
        $result = $this->service->getRecipientTypes();

        $this->assertArrayHasKey('admin', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('custom', $result);
        $this->assertArrayHasKey('conditional', $result);
    }

    // --- validateRule ---

    public function test_validate_rule_returns_empty_for_valid_data(): void
    {
        $template = \Appsolutely\AIO\Models\NotificationTemplate::factory()->create();
        $data     = [
            'name'             => 'Test Rule',
            'trigger_type'     => 'form_submission',
            'template_id'      => $template->id,
            'recipient_type'   => 'admin',
            'recipient_emails' => [],
        ];

        $errors = $this->service->validateRule($data);

        $this->assertEmpty($errors);
    }

    public function test_validate_rule_requires_name(): void
    {
        $data   = ['trigger_type' => 'form_submission', 'template_id' => 1, 'recipient_type' => 'admin'];
        $errors = $this->service->validateRule($data);

        $this->assertNotEmpty($errors);
        $this->assertContains('Rule name is required', $errors);
    }

    public function test_validate_rule_requires_trigger_type(): void
    {
        $data   = ['name' => 'Test', 'template_id' => 1, 'recipient_type' => 'admin'];
        $errors = $this->service->validateRule($data);

        $this->assertContains('Trigger type is required', $errors);
    }

    public function test_validate_rule_requires_template_id(): void
    {
        $data   = ['name' => 'Test', 'trigger_type' => 'form_submission', 'recipient_type' => 'admin'];
        $errors = $this->service->validateRule($data);

        $this->assertContains('Template is required', $errors);
    }

    public function test_validate_rule_requires_emails_for_custom_type(): void
    {
        $data = [
            'name'             => 'Test',
            'trigger_type'     => 'form_submission',
            'template_id'      => 1,
            'recipient_type'   => 'custom',
            'recipient_emails' => [],
        ];

        $errors = $this->service->validateRule($data);

        $this->assertContains('Recipient emails are required for custom type', $errors);
    }

    public function test_validate_rule_does_not_require_emails_for_admin_type(): void
    {
        $template = \Appsolutely\AIO\Models\NotificationTemplate::factory()->create();
        $data     = [
            'name'             => 'Test',
            'trigger_type'     => 'form_submission',
            'template_id'      => $template->id,
            'recipient_type'   => 'admin',
            'recipient_emails' => [],
        ];

        $errors = $this->service->validateRule($data);

        $this->assertNotContains('Recipient emails are required for custom type', $errors);
    }

    public function test_validate_rule_requires_complete_conditions(): void
    {
        $data = [
            'name'           => 'Test',
            'trigger_type'   => 'form_submission',
            'template_id'    => 1,
            'recipient_type' => 'admin',
            'conditions'     => ['field' => 'email'], // missing operator
        ];

        $errors = $this->service->validateRule($data);

        $this->assertContains('Complete condition configuration is required', $errors);
    }

    public function test_validate_rule_returns_multiple_errors(): void
    {
        $errors = $this->service->validateRule(['recipient_type' => 'admin']);

        $this->assertGreaterThan(1, count($errors));
    }

    // --- getRecipients ---

    public function test_get_recipients_returns_custom_emails(): void
    {
        $rule = NotificationRule::factory()->make([
            'recipient_type'   => 'custom',
            'recipient_emails' => ['custom@example.com'],
        ]);

        $result = $this->service->getRecipients($rule, []);

        $this->assertContains('custom@example.com', $result);
    }

    public function test_get_recipients_returns_empty_for_unknown_type(): void
    {
        $rule = NotificationRule::factory()->make([
            'recipient_type' => 'unknown_type',
        ]);

        $result = $this->service->getRecipients($rule, []);

        $this->assertEmpty($result);
    }

    public function test_get_recipients_extracts_user_email_from_data(): void
    {
        $rule = NotificationRule::factory()->make([
            'recipient_type' => 'user',
        ]);

        $result = $this->service->getRecipients($rule, ['email' => 'user@example.com']);

        $this->assertContains('user@example.com', $result);
    }

    public function test_get_recipients_ignores_invalid_email_in_data(): void
    {
        $rule = NotificationRule::factory()->make([
            'recipient_type' => 'user',
        ]);

        $result = $this->service->getRecipients($rule, ['email' => 'not-an-email']);

        $this->assertEmpty($result);
    }

    // --- evaluateConditions ---

    public function test_evaluate_conditions_delegates_to_rule_model(): void
    {
        $rule = NotificationRule::factory()->make(['conditions' => null]);

        $result = $this->service->evaluateConditions($rule, ['field' => 'value']);

        $this->assertTrue($result);
    }
}
