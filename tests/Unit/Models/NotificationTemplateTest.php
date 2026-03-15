<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    // --- render ---

    public function test_render_replaces_variables_in_subject(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => 'Hello {{name}}',
            'body_html' => '<p>Welcome</p>',
            'body_text' => 'Welcome',
        ]);

        $result = $template->render(['name' => 'Alice']);

        $this->assertEquals('Hello Alice', $result['subject']);
    }

    public function test_render_replaces_variables_in_body_html(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => 'Hello',
            'body_html' => '<p>Dear {{name}}, your order {{order_id}} is ready.</p>',
            'body_text' => 'Dear {{name}}',
        ]);

        $result = $template->render(['name' => 'Bob', 'order_id' => 'ORD-001']);

        $this->assertStringContainsString('Bob', $result['body_html']);
        $this->assertStringContainsString('ORD-001', $result['body_html']);
        $this->assertStringNotContainsString('{{name}}', $result['body_html']);
    }

    public function test_render_replaces_variables_in_body_text(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => 'Hello',
            'body_html' => '<p>Hello</p>',
            'body_text' => 'Hello {{name}}',
        ]);

        $result = $template->render(['name' => 'Carol']);

        $this->assertStringContainsString('Carol', $result['body_text']);
        $this->assertStringNotContainsString('{{name}}', $result['body_text']);
    }

    public function test_render_leaves_unmatched_placeholders_unchanged(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => 'Hello {{name}}',
            'body_html' => '<p>Test</p>',
            'body_text' => 'Test',
        ]);

        $result = $template->render([]);

        $this->assertStringContainsString('{{name}}', $result['subject']);
    }

    public function test_render_returns_all_three_parts(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => 'Subject',
            'body_html' => '<p>Html</p>',
            'body_text' => 'Text',
        ]);

        $result = $template->render([]);

        $this->assertArrayHasKey('subject', $result);
        $this->assertArrayHasKey('body_html', $result);
        $this->assertArrayHasKey('body_text', $result);
    }

    public function test_render_converts_array_values_to_json(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => 'Items: {{items}}',
            'body_html' => '<p>Items</p>',
            'body_text' => 'Items',
        ]);

        $result = $template->render(['items' => ['a', 'b', 'c']]);

        $this->assertStringContainsString('["a","b","c"]', $result['subject']);
    }

    public function test_render_handles_null_subject_and_body_html(): void
    {
        $template = NotificationTemplate::factory()->make([
            'subject'   => null,
            'body_html' => null,
            'body_text' => null,
        ]);

        $result = $template->render(['name' => 'Test']);

        $this->assertSame('', $result['subject']);
        $this->assertSame('', $result['body_html']);
        $this->assertNull($result['body_text']);
    }

    // --- getAvailableVariablesAttribute ---

    public function test_available_variables_returns_variables_array(): void
    {
        $template = NotificationTemplate::factory()->make([
            'variables' => ['name' => 'User name', 'email' => 'Email address'],
        ]);

        $result = $template->available_variables;

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
    }

    public function test_available_variables_returns_empty_array_when_null(): void
    {
        $template = NotificationTemplate::factory()->make(['variables' => null]);

        $this->assertIsArray($template->available_variables);
        $this->assertEmpty($template->available_variables);
    }

    // --- getCanDeleteAttribute ---

    public function test_can_delete_returns_true_for_non_system_template_without_rules(): void
    {
        $template = NotificationTemplate::factory()->create(['is_system' => false]);

        $this->assertTrue($template->can_delete);
    }

    public function test_can_delete_returns_false_for_system_template(): void
    {
        $template = NotificationTemplate::factory()->create(['is_system' => true]);

        $this->assertFalse($template->can_delete);
    }

    public function test_can_delete_returns_false_when_template_has_rules(): void
    {
        $template = NotificationTemplate::factory()->create(['is_system' => false]);
        NotificationRule::factory()->create(['template_id' => $template->id]);

        $this->assertFalse($template->can_delete);
    }
}
