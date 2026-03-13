<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Services\NotificationTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationTemplateService::class);
    }

    // --- getAvailableVariables ---

    public function test_get_available_variables_returns_array_for_form_category(): void
    {
        $result = $this->service->getAvailableVariables('form');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('form_name', $result);
        $this->assertArrayHasKey('user_name', $result);
        $this->assertArrayHasKey('form_fields_html', $result);
        $this->assertArrayHasKey('form_fields_text', $result);
    }

    public function test_get_available_variables_returns_array_for_user_category(): void
    {
        $result = $this->service->getAvailableVariables('user');

        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('full_name', $result);
        $this->assertArrayHasKey('verification_link', $result);
    }

    public function test_get_available_variables_returns_array_for_order_category(): void
    {
        $result = $this->service->getAvailableVariables('order');

        $this->assertArrayHasKey('order_number', $result);
        $this->assertArrayHasKey('order_total', $result);
        $this->assertArrayHasKey('customer_name', $result);
    }

    public function test_get_available_variables_returns_array_for_system_category(): void
    {
        $result = $this->service->getAvailableVariables('system');

        $this->assertArrayHasKey('site_name', $result);
        $this->assertArrayHasKey('site_url', $result);
        $this->assertArrayHasKey('admin_email', $result);
    }

    public function test_get_available_variables_returns_empty_for_unknown_category(): void
    {
        $result = $this->service->getAvailableVariables('unknown_category');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_get_available_variables_descriptions_are_strings(): void
    {
        $result = $this->service->getAvailableVariables('form');

        foreach ($result as $key => $description) {
            $this->assertIsString($key);
            $this->assertIsString($description);
        }
    }

    // --- getSampleVariables ---

    public function test_get_sample_variables_returns_form_samples(): void
    {
        $result = $this->service->getSampleVariables('form');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('form_name', $result);
        $this->assertArrayHasKey('user_name', $result);
        $this->assertIsString($result['form_name']);
    }

    public function test_get_sample_variables_returns_user_samples(): void
    {
        $result = $this->service->getSampleVariables('user');

        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertStringContainsString('@', $result['email']);
    }

    public function test_get_sample_variables_returns_system_samples(): void
    {
        $result = $this->service->getSampleVariables('system');

        $this->assertArrayHasKey('site_name', $result);
        $this->assertArrayHasKey('current_date', $result);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $result['current_date']);
    }

    public function test_get_sample_variables_returns_empty_for_unknown(): void
    {
        $result = $this->service->getSampleVariables('unknown');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- validateTemplate ---

    public function test_validate_template_returns_empty_array_for_valid_template(): void
    {
        $content  = 'Hello {name}, your order {order_id} is ready.';
        $allowed  = ['name' => 'User name', 'order_id' => 'Order ID'];

        $errors = $this->service->validateTemplate($content, $allowed);

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    public function test_validate_template_returns_error_for_undefined_variable(): void
    {
        $content  = 'Hello {name}, your order {unknown_var} is ready.';
        $allowed  = ['name' => 'User name'];

        $errors = $this->service->validateTemplate($content, $allowed);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('unknown_var', $errors[0]);
    }

    public function test_validate_template_returns_multiple_errors(): void
    {
        $content = 'Hello {var1} and {var2} and {var3}.';
        $allowed = [];

        $errors = $this->service->validateTemplate($content, $allowed);

        $this->assertCount(3, $errors);
    }

    public function test_validate_template_ignores_content_without_variables(): void
    {
        $content = 'Hello world, no variables here.';
        $allowed = [];

        $errors = $this->service->validateTemplate($content, $allowed);

        $this->assertEmpty($errors);
    }

    public function test_validate_template_with_empty_content(): void
    {
        $errors = $this->service->validateTemplate('', ['name' => 'User name']);

        $this->assertEmpty($errors);
    }

    // --- createTemplate / duplicateTemplate (DB interaction) ---

    public function test_create_template_persists_to_database(): void
    {
        $data = [
            'name'      => 'Test Template',
            'category'  => 'form',
            'subject'   => 'Test Subject',
            'body_html' => '<p>Hello {user_name}</p>',
            'body_text' => 'Hello {user_name}',
        ];

        $template = $this->service->createTemplate($data);

        $this->assertInstanceOf(NotificationTemplate::class, $template);
        $this->assertDatabaseHas('notification_templates', ['name' => 'Test Template']);
    }

    public function test_duplicate_template_creates_new_record(): void
    {
        $original = NotificationTemplate::factory()->create(['name' => 'Original']);

        $copy = $this->service->duplicateTemplate($original);

        $this->assertNotEquals($original->id, $copy->id);
        $this->assertDatabaseCount('notification_templates', 2);
    }
}
