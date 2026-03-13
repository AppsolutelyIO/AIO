<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationRuleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NotificationRuleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(NotificationRuleRepository::class);
    }

    // --- findByTrigger ---

    public function test_find_by_trigger_returns_matching_active_rules(): void
    {
        $rule = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $result = $this->repository->findByTrigger('form_submission', 'contact-form');

        $this->assertCount(1, $result);
        $this->assertEquals($rule->id, $result->first()->id);
    }

    public function test_find_by_trigger_returns_wildcard_rules(): void
    {
        $wildcardRule = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => '*',
            'status'            => Status::ACTIVE,
        ]);

        $result = $this->repository->findByTrigger('form_submission', 'any-form');

        $this->assertCount(1, $result);
        $this->assertEquals($wildcardRule->id, $result->first()->id);
    }

    public function test_find_by_trigger_returns_both_exact_and_wildcard_rules(): void
    {
        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);
        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => '*',
            'status'            => Status::ACTIVE,
        ]);

        $result = $this->repository->findByTrigger('form_submission', 'contact-form');

        $this->assertCount(2, $result);
    }

    public function test_find_by_trigger_excludes_inactive_rules(): void
    {
        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::INACTIVE,
        ]);

        $result = $this->repository->findByTrigger('form_submission', 'contact-form');

        $this->assertCount(0, $result);
    }

    public function test_find_by_trigger_excludes_different_trigger_types(): void
    {
        NotificationRule::factory()->create([
            'trigger_type'      => 'user_registration',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $result = $this->repository->findByTrigger('form_submission', 'contact-form');

        $this->assertCount(0, $result);
    }

    public function test_find_by_trigger_eager_loads_template(): void
    {
        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $result = $this->repository->findByTrigger('form_submission', 'contact-form');

        $this->assertTrue($result->first()->relationLoaded('template'));
    }

    // --- getActiveWithTemplates ---

    public function test_get_active_with_templates_returns_only_active(): void
    {
        NotificationRule::factory()->create(['status' => Status::ACTIVE]);
        NotificationRule::factory()->create(['status' => Status::ACTIVE]);
        NotificationRule::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getActiveWithTemplates();

        $this->assertCount(2, $result);
    }

    public function test_get_active_with_templates_eager_loads_template(): void
    {
        NotificationRule::factory()->create(['status' => Status::ACTIVE]);

        $result = $this->repository->getActiveWithTemplates();

        $this->assertTrue($result->first()->relationLoaded('template'));
    }

    // --- getByTriggerType ---

    public function test_get_by_trigger_type_returns_matching_rules(): void
    {
        NotificationRule::factory()->create(['trigger_type' => 'form_submission']);
        NotificationRule::factory()->create(['trigger_type' => 'form_submission']);
        NotificationRule::factory()->create(['trigger_type' => 'user_registration']);

        $result = $this->repository->getByTriggerType('form_submission');

        $this->assertCount(2, $result);
    }

    public function test_get_by_trigger_type_returns_empty_for_unknown_type(): void
    {
        $result = $this->repository->getByTriggerType('nonexistent_type');

        $this->assertCount(0, $result);
    }

    // --- getByTemplate ---

    public function test_get_by_template_returns_rules_for_template(): void
    {
        $template = NotificationTemplate::factory()->create();
        $rule1    = NotificationRule::factory()->create(['template_id' => $template->id]);
        $rule2    = NotificationRule::factory()->create(['template_id' => $template->id]);
        NotificationRule::factory()->create(); // different template

        $result = $this->repository->getByTemplate($template->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $rule1->id));
        $this->assertTrue($result->contains('id', $rule2->id));
    }

    public function test_get_by_template_returns_empty_for_nonexistent_template(): void
    {
        $result = $this->repository->getByTemplate(99999);

        $this->assertCount(0, $result);
    }

    // --- duplicate ---

    public function test_duplicate_creates_new_rule_with_copy_suffix(): void
    {
        $original = NotificationRule::factory()->create([
            'name'   => 'My Rule',
            'status' => Status::ACTIVE,
        ]);

        $copy = $this->repository->duplicate($original->id);

        $this->assertNotEquals($original->id, $copy->id);
        $this->assertEquals('My Rule (Copy)', $copy->name);
    }

    public function test_duplicate_creates_inactive_copy(): void
    {
        $original = NotificationRule::factory()->create(['status' => Status::ACTIVE]);

        $copy = $this->repository->duplicate($original->id);

        $this->assertEquals(Status::INACTIVE, $copy->status);
    }

    public function test_duplicate_preserves_trigger_and_template(): void
    {
        $original = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
        ]);

        $copy = $this->repository->duplicate($original->id);

        $this->assertEquals($original->trigger_type, $copy->trigger_type);
        $this->assertEquals($original->trigger_reference, $copy->trigger_reference);
        $this->assertEquals($original->template_id, $copy->template_id);
    }

    // --- getDelayedRules ---

    public function test_get_delayed_rules_returns_only_rules_with_delay(): void
    {
        NotificationRule::factory()->create(['delay_minutes' => 60]);
        NotificationRule::factory()->create(['delay_minutes' => 30]);
        NotificationRule::factory()->create(['delay_minutes' => 0]);

        $result = $this->repository->getDelayedRules();

        $this->assertCount(2, $result);
    }

    public function test_get_delayed_rules_orders_by_delay_minutes(): void
    {
        $rule60  = NotificationRule::factory()->create(['delay_minutes' => 60]);
        $rule120 = NotificationRule::factory()->create(['delay_minutes' => 120]);
        $rule30  = NotificationRule::factory()->create(['delay_minutes' => 30]);

        $result = $this->repository->getDelayedRules();

        $this->assertEquals($rule30->id, $result->first()->id);
        $this->assertEquals($rule60->id, $result->get(1)->id);
        $this->assertEquals($rule120->id, $result->last()->id);
    }

    // --- bulkUpdateStatus ---

    public function test_bulk_update_status_updates_specified_rules(): void
    {
        $rule1 = NotificationRule::factory()->create(['status' => Status::ACTIVE]);
        $rule2 = NotificationRule::factory()->create(['status' => Status::ACTIVE]);
        $rule3 = NotificationRule::factory()->create(['status' => Status::ACTIVE]);

        $count = $this->repository->bulkUpdateStatus([$rule1->id, $rule2->id], Status::INACTIVE->value);

        $this->assertEquals(2, $count);
        $this->assertEquals(Status::INACTIVE, $rule1->fresh()->status);
        $this->assertEquals(Status::INACTIVE, $rule2->fresh()->status);
        $this->assertEquals(Status::ACTIVE, $rule3->fresh()->status);
    }

    // --- getArr ---

    public function test_get_arr_converts_comma_separated_emails_to_array(): void
    {
        $data = ['recipient_emails' => 'alice@example.com, bob@example.com, carol@example.com'];

        $result = $this->repository->getArr($data);

        $this->assertIsArray($result['recipient_emails']);
        $this->assertCount(3, $result['recipient_emails']);
        $this->assertContains('alice@example.com', $result['recipient_emails']);
        $this->assertContains('bob@example.com', $result['recipient_emails']);
    }

    public function test_get_arr_leaves_array_emails_unchanged(): void
    {
        $emails = ['alice@example.com', 'bob@example.com'];
        $data   = ['recipient_emails' => $emails];

        $result = $this->repository->getArr($data);

        $this->assertSame($emails, $result['recipient_emails']);
    }

    public function test_get_arr_converts_json_conditions_string_to_array(): void
    {
        $conditions = ['field' => 'email', 'operator' => 'equals', 'value' => 'test@test.com'];
        $data       = ['conditions' => json_encode($conditions)];

        $result = $this->repository->getArr($data);

        $this->assertIsArray($result['conditions']);
        $this->assertEquals('email', $result['conditions']['field']);
    }

    public function test_get_arr_leaves_array_conditions_unchanged(): void
    {
        $conditions = ['field' => 'name', 'operator' => 'contains', 'value' => 'John'];
        $data       = ['conditions' => $conditions];

        $result = $this->repository->getArr($data);

        $this->assertSame($conditions, $result['conditions']);
    }

    public function test_get_arr_handles_data_without_emails_or_conditions(): void
    {
        $data = ['name' => 'Test Rule', 'trigger_type' => 'form_submission'];

        $result = $this->repository->getArr($data);

        $this->assertEquals($data, $result);
    }

    // --- getTriggerTypesWithCounts ---

    public function test_get_trigger_types_with_counts_returns_correct_counts(): void
    {
        NotificationRule::factory()->create(['trigger_type' => 'form_submission', 'status' => Status::ACTIVE]);
        NotificationRule::factory()->create(['trigger_type' => 'form_submission', 'status' => Status::ACTIVE]);
        NotificationRule::factory()->create(['trigger_type' => 'user_registration', 'status' => Status::ACTIVE]);

        $result = $this->repository->getTriggerTypesWithCounts();

        $this->assertIsArray($result);
        $this->assertEquals(2, $result['form_submission']);
        $this->assertEquals(1, $result['user_registration']);
    }

    public function test_get_trigger_types_with_counts_excludes_inactive(): void
    {
        NotificationRule::factory()->create(['trigger_type' => 'form_submission', 'status' => Status::ACTIVE]);
        NotificationRule::factory()->create(['trigger_type' => 'form_submission', 'status' => Status::INACTIVE]);

        $result = $this->repository->getTriggerTypesWithCounts();

        $this->assertEquals(1, $result['form_submission']);
    }

    // --- getRulesNeedingTemplate ---

    public function test_get_rules_needing_template_returns_collection(): void
    {
        NotificationRule::factory()->create();
        NotificationRule::factory()->create();

        $result = $this->repository->getRulesNeedingTemplate();

        // All rules have templates (template_id NOT NULL + cascade), so result is empty
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertCount(0, $result);
    }

    // --- createRule ---

    public function test_create_rule_creates_and_returns_rule(): void
    {
        $template = NotificationTemplate::factory()->create();

        $rule = $this->repository->createRule([
            'name'              => 'New Rule',
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact',
            'template_id'       => $template->id,
            'recipient_type'    => 'admin',
            'recipient_emails'  => 'admin@example.com',
            'status'            => Status::ACTIVE,
        ]);

        $this->assertInstanceOf(NotificationRule::class, $rule);
        $this->assertEquals('New Rule', $rule->name);
        $this->assertDatabaseHas('notification_rules', ['name' => 'New Rule']);
    }

    // --- updateRule ---

    public function test_update_rule_updates_and_returns_rule(): void
    {
        $rule = NotificationRule::factory()->create(['name' => 'Old Name']);

        $result = $this->repository->updateRule($rule->id, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $result->name);
        $this->assertDatabaseHas('notification_rules', ['id' => $rule->id, 'name' => 'Updated Name']);
    }

    // --- getWithUsageStats ---

    public function test_get_with_usage_stats_returns_rules_with_counts(): void
    {
        $rule = NotificationRule::factory()->create();
        \Appsolutely\AIO\Models\NotificationQueue::factory()->sent()->count(3)->create(['rule_id' => $rule->id]);
        \Appsolutely\AIO\Models\NotificationQueue::factory()->pending()->count(2)->create(['rule_id' => $rule->id]);

        $result = $this->repository->getWithUsageStats();

        $this->assertCount(1, $result);
        $ruleResult = $result->first();
        $this->assertEquals(3, $ruleResult->total_sent);
        $this->assertEquals(2, $ruleResult->pending_count);
    }

    // --- getByRecipientType ---

    public function test_get_by_recipient_type_returns_matching_rules(): void
    {
        NotificationRule::factory()->create(['recipient_type' => 'admin']);
        NotificationRule::factory()->create(['recipient_type' => 'admin']);
        NotificationRule::factory()->create(['recipient_type' => 'submitter']);

        $result = $this->repository->getByRecipientType('admin');

        $this->assertCount(2, $result);
    }

    // --- getConditionalRulesForField ---

    public function test_get_conditional_rules_for_field_returns_matching_rules(): void
    {
        NotificationRule::factory()->create([
            'recipient_type' => 'conditional',
            'conditions'     => [['field' => 'email', 'operator' => 'equals', 'value' => 'test@test.com']],
        ]);
        NotificationRule::factory()->create([
            'recipient_type' => 'conditional',
            'conditions'     => [['field' => 'name', 'operator' => 'contains', 'value' => 'John']],
        ]);

        $result = $this->repository->getConditionalRulesForField('email');

        $this->assertCount(1, $result);
    }
}
