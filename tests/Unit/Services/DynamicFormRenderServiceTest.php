<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\DynamicFormRenderService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class DynamicFormRenderServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicFormRenderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicFormRenderService::class);
    }

    // --- renderForm ---

    public function test_render_form_returns_form_html(): void
    {
        $form = Form::factory()->create();

        $html = $this->service->renderForm($form);

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('</form>', $html);
        $this->assertStringContainsString('Submit', $html);
    }

    public function test_render_form_includes_csrf_field(): void
    {
        $form = Form::factory()->create();

        $html = $this->service->renderForm($form);

        $this->assertStringContainsString('_token', $html);
    }

    public function test_render_form_includes_fields(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'email_address',
            'label'   => 'Email Address',
            'type'    => 'email',
        ]);
        $form->load('fields');

        $html = $this->service->renderForm($form);

        $this->assertStringContainsString('email_address', $html);
        $this->assertStringContainsString('Email Address', $html);
    }

    // --- renderField ---

    public function test_render_field_text_type(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'full_name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);

        $html = $this->service->renderField($field, 'John Doe');

        $this->assertStringContainsString('type=\'text\'', $html);
        $this->assertStringContainsString('full_name', $html);
        $this->assertStringContainsString('John Doe', $html);
    }

    public function test_render_field_textarea_type(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'message',
            'label'   => 'Message',
            'type'    => 'textarea',
        ]);

        $html = $this->service->renderField($field, 'Hello World');

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('Hello World', $html);
    }

    public function test_render_field_hidden_type_returns_minimal_html(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'token',
            'label'   => 'Token',
            'type'    => 'hidden',
        ]);

        $html = $this->service->renderField($field, 'abc123');

        $this->assertStringContainsString('type=\'hidden\'', $html);
        $this->assertStringContainsString('abc123', $html);
        // Hidden fields should NOT have labels or error wrappers
        $this->assertStringNotContainsString('<label', $html);
        $this->assertStringNotContainsString('form-group', $html);
    }

    public function test_render_field_shows_required_indicator(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'name',
            'label'    => 'Name',
            'type'     => 'text',
            'required' => true,
        ]);

        $html = $this->service->renderField($field);

        $this->assertStringContainsString('*', $html);
        $this->assertStringContainsString('required', $html);
    }

    public function test_render_field_shows_error(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'email',
            'label'   => 'Email',
            'type'    => 'email',
        ]);

        $html = $this->service->renderField($field, null, 'Invalid email address');

        $this->assertStringContainsString('Invalid email address', $html);
    }

    public function test_render_field_select_type(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'country',
            'label'   => 'Country',
            'type'    => 'select',
            'options' => ['US', 'UK', 'CA'],
        ]);

        $html = $this->service->renderField($field, 'UK');

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('US', $html);
        $this->assertStringContainsString('UK', $html);
        $this->assertStringContainsString('selected', $html);
    }

    // --- getFields ---

    public function test_get_fields_returns_array_of_field_configs(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'username',
            'label'   => 'Username',
            'type'    => 'text',
            'sort'    => 1,
        ]);
        $form->load('fields');

        $fields = $this->service->getFields($form);

        $this->assertIsArray($fields);
        $this->assertArrayHasKey('username', $fields);
        $this->assertEquals(FormFieldType::Text, $fields['username']['type']);
        $this->assertEquals('Username', $fields['username']['label']);
    }

    public function test_get_fields_returns_empty_array_when_no_fields(): void
    {
        $form = Form::factory()->create();
        $form->load('fields');

        $fields = $this->service->getFields($form);

        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
    }
}
