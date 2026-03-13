<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Services\PageBlockSchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Appsolutely\AIO\Tests\TestCase;

final class PageBlockSchemaServiceTest extends TestCase
{
    use RefreshDatabase;

    private PageBlockSchemaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBlockSchemaService::class);
    }

    private function makeBlock(array $schema): PageBlock
    {
        $block         = new PageBlock();
        $block->schema = $schema;

        return $block;
    }

    // --- getBlockSchema ---

    public function test_get_block_schema_returns_array_from_array_schema(): void
    {
        $schema = ['title' => ['type' => 'text', 'label' => 'Title']];
        $block  = $this->makeBlock($schema);

        $result = $this->service->getBlockSchema($block);

        $this->assertEquals($schema, $result);
    }

    public function test_get_block_schema_returns_empty_for_empty_schema(): void
    {
        $block         = new PageBlock();
        $block->schema = [];

        $result = $this->service->getBlockSchema($block);

        $this->assertEquals([], $result);
    }

    // --- getDefaultValues ---

    public function test_get_default_values_returns_field_defaults(): void
    {
        $schema = [
            'title'      => ['type' => 'text', 'default' => 'Untitled'],
            'count'      => ['type' => 'number', 'default' => 0],
            'no_default' => ['type' => 'text'],
        ];

        $result = $this->service->getDefaultValues($schema);

        $this->assertEquals('Untitled', $result['title']);
        $this->assertEquals(0, $result['count']);
        $this->assertArrayNotHasKey('no_default', $result);
    }

    public function test_get_default_values_returns_empty_array_for_table_type(): void
    {
        $schema = [
            'rows' => ['type' => 'table', 'fields' => ['name' => ['type' => 'text']]],
        ];

        $result = $this->service->getDefaultValues($schema);

        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals([], $result['rows']);
    }

    public function test_get_default_values_returns_empty_for_empty_schema(): void
    {
        $result = $this->service->getDefaultValues([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // --- mergeWithDefaults ---

    public function test_merge_with_defaults_adds_missing_defaults(): void
    {
        $schema = [
            'title'   => ['type' => 'text', 'default' => 'Untitled'],
            'content' => ['type' => 'textarea', 'default' => ''],
        ];
        $values = ['title' => 'My Title'];

        $result = $this->service->mergeWithDefaults($schema, $values);

        $this->assertEquals('My Title', $result['title']);
        $this->assertEquals('', $result['content']);
    }

    public function test_merge_with_defaults_does_not_overwrite_provided_values(): void
    {
        $schema = ['title' => ['type' => 'text', 'default' => 'Default']];
        $values = ['title' => 'Custom'];

        $result = $this->service->mergeWithDefaults($schema, $values);

        $this->assertEquals('Custom', $result['title']);
    }

    // --- validateSchemaValues ---

    public function test_validate_schema_values_passes_for_valid_data(): void
    {
        $schema = [
            'name' => ['type' => 'text', 'required' => true, 'label' => 'Name'],
        ];
        $values = ['name' => 'John'];

        $result = $this->service->validateSchemaValues($schema, $values);

        $this->assertEquals(['name' => 'John'], $result);
    }

    public function test_validate_schema_values_throws_for_required_missing(): void
    {
        $schema = [
            'name' => ['type' => 'text', 'required' => true, 'label' => 'Name'],
        ];

        $this->expectException(ValidationException::class);

        $this->service->validateSchemaValues($schema, []);
    }

    public function test_validate_schema_values_passes_nullable_when_missing(): void
    {
        $schema = [
            'optional' => ['type' => 'text', 'required' => false, 'label' => 'Optional'],
        ];

        $result = $this->service->validateSchemaValues($schema, []);

        $this->assertIsArray($result);
    }

    // --- generateFormConfig ---

    public function test_generate_form_config_returns_config_for_each_field(): void
    {
        $schema = [
            'title'   => ['type' => 'text', 'label' => 'Title', 'required' => true],
            'content' => ['type' => 'textarea', 'label' => 'Content'],
        ];

        $result = $this->service->generateFormConfig($schema);

        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('content', $result);
        $this->assertEquals('text', $result['title']['type']);
        $this->assertEquals('Title', $result['title']['label']);
        $this->assertTrue($result['title']['required']);
    }

    public function test_generate_form_config_returns_empty_for_empty_schema(): void
    {
        $result = $this->service->generateFormConfig([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
