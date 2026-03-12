<?php

namespace Appsolutely\AIO\Tests\Integration\Widgets;

use Appsolutely\AIO\Tests\Integration\TestCase;
use Appsolutely\AIO\Widgets\Table;

class TableTest extends TestCase
{
    // --- Construction ---

    public function test_constructor_rows_only()
    {
        $table = new Table(['key' => 'value']);
        $html = $table->render();

        $this->assertStringContainsString('key', $html);
        $this->assertStringContainsString('value', $html);
    }

    public function test_constructor_with_headers_and_rows()
    {
        $table = new Table(['Name', 'Age'], [['Alice', 25], ['Bob', 30]]);
        $html = $table->render();

        $this->assertStringContainsString('Name', $html);
        $this->assertStringContainsString('Age', $html);
        $this->assertStringContainsString('Alice', $html);
        $this->assertStringContainsString('25', $html);
    }

    // --- Table class ---

    public function test_default_class()
    {
        $table = new Table(['a' => 'b']);
        $html = $table->render();

        $this->assertStringContainsString('table', $html);
        $this->assertStringContainsString('default-table', $html);
    }

    // --- setHeaders ---

    public function test_set_headers()
    {
        $table = new Table();
        $table->setHeaders(['Col1', 'Col2']);
        $table->setRows([['A', 'B']]);
        $html = $table->render();

        $this->assertStringContainsString('Col1', $html);
        $this->assertStringContainsString('Col2', $html);
    }

    // --- setRows ---

    public function test_set_rows_key_value()
    {
        $table = new Table();
        $table->setRows(['name' => 'Alice', 'age' => '25']);
        $html = $table->render();

        $this->assertStringContainsString('name', $html);
        $this->assertStringContainsString('Alice', $html);
    }

    public function test_set_rows_array()
    {
        $table = new Table();
        $table->setRows([['A', 'B'], ['C', 'D']]);
        $html = $table->render();

        $this->assertStringContainsString('A', $html);
        $this->assertStringContainsString('D', $html);
    }

    // --- Style ---

    public function test_set_style()
    {
        $table = new Table(['a' => 'b']);
        $table->setStyle(['table-striped', 'table-hover']);
        $html = $table->render();

        $this->assertStringContainsString('table-striped', $html);
        $this->assertStringContainsString('table-hover', $html);
    }

    // --- Border ---

    public function test_with_border()
    {
        $table = (new Table(['a' => 'b']))->withBorder();
        $html = $table->render();

        $this->assertStringContainsString('table-bordered', $html);
    }

    // --- Make ---

    public function test_make_factory()
    {
        $table = Table::make(['Name', 'Value'], [['foo', 'bar']]);
        $html = $table->render();

        $this->assertStringContainsString('Name', $html);
        $this->assertStringContainsString('bar', $html);
    }

    // --- JSON array rows ---

    public function test_array_values_rendered_as_json()
    {
        $table = new Table();
        $table->setRows(['tags' => ['php', 'laravel']]);
        $html = $table->render();

        // Non-assoc arrays within assoc rows are JSON encoded
        $this->assertStringContainsString('php', $html);
        $this->assertStringContainsString('laravel', $html);
    }
}
