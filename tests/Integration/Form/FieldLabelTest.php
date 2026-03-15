<?php

namespace Appsolutely\AIO\Tests\Integration\Form;

use Appsolutely\AIO\Form\Field;
use Appsolutely\AIO\Tests\Integration\TestCase;

class FieldLabelTest extends TestCase
{
    private function createField(): Field
    {
        // Field constructor requires column and arguments
        return new class('test_column', ['Test Label']) extends Field
        {
            // Minimal stub - Field is abstract-like but actually concrete
        };
    }

    // --- Field::label() as getter ---

    public function test_label_returns_current_label_when_no_argument()
    {
        $field = $this->createField();
        $label = $field->label();
        $this->assertSame('Test Label', $label);
    }

    // --- Field::label() as setter ---

    public function test_label_sets_new_label()
    {
        $field  = $this->createField();
        $result = $field->label('New Label');
        $this->assertSame($field, $result);
        $this->assertSame('New Label', $field->label());
    }

    public function test_label_with_closure()
    {
        $field = $this->createField();
        $field->label(function ($current) {
            return $current . ' Modified';
        });
        $this->assertSame('Test Label Modified', $field->label());
    }

    // --- Field::label() null vs empty string distinction ---

    public function test_label_with_null_acts_as_getter()
    {
        $field  = $this->createField();
        $result = $field->label(null);
        $this->assertSame('Test Label', $result);
    }

    public function test_label_with_empty_string_acts_as_setter()
    {
        $field  = $this->createField();
        $result = $field->label('');
        // After fix: empty string should set the label, not act as getter
        $this->assertSame($field, $result);
        $this->assertSame('', $field->label());
    }

    public function test_label_with_zero_acts_as_setter()
    {
        $field  = $this->createField();
        $result = $field->label('0');
        $this->assertSame($field, $result);
        $this->assertSame('0', $field->label());
    }
}
