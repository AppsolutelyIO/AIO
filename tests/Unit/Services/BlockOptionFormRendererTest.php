<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\BlockOptionFormRenderer;
use Appsolutely\AIO\Tests\TestCase;

final class BlockOptionFormRendererTest extends TestCase
{
    private BlockOptionFormRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = app(BlockOptionFormRenderer::class);
    }

    // --- render ---

    public function test_render_returns_placeholder_for_empty_definition(): void
    {
        $result = $this->renderer->render([], []);

        $this->assertStringContainsString('No options defined', $result);
    }

    public function test_render_returns_html_for_text_field(): void
    {
        $definition = [
            'title' => ['type' => 'text', 'label' => 'Title'],
        ];

        $result = $this->renderer->render($definition, ['title' => 'Hello']);

        $this->assertStringContainsString('Title', $result);
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('data-pb-field="title"', $result);
    }

    public function test_render_uses_default_value_when_no_value_given(): void
    {
        $definition = [
            'count' => ['type' => 'number', 'label' => 'Count', 'default' => 5],
        ];

        $result = $this->renderer->render($definition, []);

        $this->assertStringContainsString('value="5"', $result);
    }

    public function test_render_outputs_boolean_checkbox(): void
    {
        $definition = [
            'active' => ['type' => 'boolean', 'label' => 'Active'],
        ];

        $result = $this->renderer->render($definition, ['active' => true]);

        $this->assertStringContainsString('type="checkbox"', $result);
        $this->assertStringContainsString('checked', $result);
    }

    public function test_render_outputs_boolean_unchecked_when_false(): void
    {
        $definition = [
            'active' => ['type' => 'boolean', 'label' => 'Active'],
        ];

        $result = $this->renderer->render($definition, ['active' => false]);

        $this->assertStringContainsString('type="checkbox"', $result);
        $this->assertStringNotContainsString('checked', $result);
    }

    public function test_render_outputs_select_with_options(): void
    {
        $definition = [
            'color' => [
                'type'    => 'select',
                'label'   => 'Color',
                'options' => ['red', 'green', 'blue'],
            ],
        ];

        $result = $this->renderer->render($definition, ['color' => 'green']);

        $this->assertStringContainsString('<select', $result);
        $this->assertStringContainsString('red', $result);
        $this->assertStringContainsString('green', $result);
        $this->assertStringContainsString('blue', $result);
        $this->assertStringContainsString('selected', $result);
    }

    public function test_render_outputs_textarea(): void
    {
        $definition = [
            'bio' => ['type' => 'textarea', 'label' => 'Bio'],
        ];

        $result = $this->renderer->render($definition, ['bio' => 'Some text']);

        $this->assertStringContainsString('<textarea', $result);
        $this->assertStringContainsString('Some text', $result);
    }

    public function test_render_outputs_object_field_with_subfields(): void
    {
        $definition = [
            'settings' => [
                'type'   => 'object',
                'label'  => 'Settings',
                'fields' => [
                    'width'  => ['type' => 'number', 'label' => 'Width'],
                    'height' => ['type' => 'number', 'label' => 'Height'],
                ],
            ],
        ];

        $result = $this->renderer->render($definition, ['settings' => ['width' => 100, 'height' => 200]]);

        $this->assertStringContainsString('data-pb-object="settings"', $result);
        $this->assertStringContainsString('data-pb-sub-field="width"', $result);
        $this->assertStringContainsString('data-pb-sub-field="height"', $result);
        $this->assertStringContainsString('100', $result);
        $this->assertStringContainsString('200', $result);
    }

    public function test_render_outputs_table_field_with_horizontal_layout(): void
    {
        $definition = [
            'items' => [
                'type'   => 'table',
                'label'  => 'Items',
                'fields' => [
                    'name'  => ['type' => 'text', 'label' => 'Name'],
                    'price' => ['type' => 'number', 'label' => 'Price'],
                ],
            ],
        ];

        $result = $this->renderer->render($definition, ['items' => [
            ['name' => 'Widget', 'price' => 10],
        ]]);

        $this->assertStringContainsString('data-pb-table="items"', $result);
        $this->assertStringContainsString('data-pb-add-row="items"', $result);
        $this->assertStringContainsString('Widget', $result);
    }

    public function test_render_outputs_table_with_vertical_layout_when_many_columns(): void
    {
        $definition = [
            'rows' => [
                'type'   => 'table',
                'label'  => 'Rows',
                'fields' => [
                    'col1' => ['type' => 'text', 'label' => 'Col1'],
                    'col2' => ['type' => 'text', 'label' => 'Col2'],
                    'col3' => ['type' => 'text', 'label' => 'Col3'],
                    'col4' => ['type' => 'text', 'label' => 'Col4'],
                ],
            ],
        ];

        $result = $this->renderer->render($definition, ['rows' => []]);

        $this->assertStringContainsString('data-pb-table="rows"', $result);
    }

    public function test_render_escapes_html_in_values(): void
    {
        $definition = [
            'title' => ['type' => 'text', 'label' => 'Title'],
        ];

        $result = $this->renderer->render($definition, ['title' => '<script>alert("xss")</script>']);

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_render_outputs_number_field_with_min_max(): void
    {
        $definition = [
            'qty' => ['type' => 'number', 'label' => 'Qty', 'min' => 1, 'max' => 100],
        ];

        $result = $this->renderer->render($definition, []);

        $this->assertStringContainsString('type="number"', $result);
        $this->assertStringContainsString('min="1"', $result);
        $this->assertStringContainsString('max="100"', $result);
    }

    public function test_render_outputs_email_field(): void
    {
        $definition = [
            'email' => ['type' => 'email', 'label' => 'Email'],
        ];

        $result = $this->renderer->render($definition, ['email' => 'test@example.com']);

        $this->assertStringContainsString('type="email"', $result);
        $this->assertStringContainsString('test@example.com', $result);
    }

    public function test_render_outputs_file_upload_field(): void
    {
        $definition = [
            'image' => ['type' => 'image', 'label' => 'Image'],
        ];

        $result = $this->renderer->render($definition, []);

        $this->assertStringContainsString('type="file"', $result);
        $this->assertStringContainsString('accept="image/*"', $result);
    }

    public function test_render_outputs_description_when_provided(): void
    {
        $definition = [
            'title' => ['type' => 'text', 'label' => 'Title', 'description' => 'Enter a title here'],
        ];

        $result = $this->renderer->render($definition, []);

        $this->assertStringContainsString('Enter a title here', $result);
    }

    public function test_render_handles_select_with_array_options(): void
    {
        $definition = [
            'type' => [
                'type'    => 'select',
                'label'   => 'Type',
                'options' => [
                    ['value' => 'a', 'label' => 'Option A'],
                    ['value' => 'b', 'label' => 'Option B'],
                ],
            ],
        ];

        $result = $this->renderer->render($definition, ['type' => 'a']);

        $this->assertStringContainsString('Option A', $result);
        $this->assertStringContainsString('Option B', $result);
    }
}
