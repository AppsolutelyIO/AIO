<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Repositories\FormFieldRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class FormFieldRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FormFieldRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(FormFieldRepository::class);
    }

    // --- getFieldsByForm ---

    public function test_get_fields_by_form_returns_fields_for_form(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->count(3)->create(['form_id' => $form->id]);
        FormField::factory()->count(2)->create(); // other form

        $result = $this->repository->getFieldsByForm($form->id);

        $this->assertCount(3, $result);
        $result->each(fn ($f) => $this->assertEquals($form->id, $f->form_id));
    }

    public function test_get_fields_by_form_returns_empty_for_no_fields(): void
    {
        $form   = Form::factory()->create();
        $result = $this->repository->getFieldsByForm($form->id);

        $this->assertCount(0, $result);
    }

    public function test_get_fields_by_form_ordered_by_sort(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'sort' => 3]);
        FormField::factory()->create(['form_id' => $form->id, 'sort' => 1]);
        FormField::factory()->create(['form_id' => $form->id, 'sort' => 2]);

        $result = $this->repository->getFieldsByForm($form->id);

        $this->assertEquals(1, $result->get(0)->sort);
        $this->assertEquals(2, $result->get(1)->sort);
        $this->assertEquals(3, $result->get(2)->sort);
    }

    // --- getRequiredFields ---

    public function test_get_required_fields_returns_only_required(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'required' => true]);
        FormField::factory()->create(['form_id' => $form->id, 'required' => true]);
        FormField::factory()->create(['form_id' => $form->id, 'required' => false]);

        $result = $this->repository->getRequiredFields($form->id);

        $this->assertCount(2, $result);
        $result->each(fn ($f) => $this->assertTrue($f->required));
    }

    public function test_get_required_fields_returns_empty_when_none_required(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->count(3)->create(['form_id' => $form->id, 'required' => false]);

        $result = $this->repository->getRequiredFields($form->id);

        $this->assertCount(0, $result);
    }

    // --- getFieldByName ---

    public function test_get_field_by_name_finds_field(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create(['form_id' => $form->id, 'name' => 'email']);

        $result = $this->repository->getFieldByName($form->id, 'email');

        $this->assertInstanceOf(FormField::class, $result);
        $this->assertEquals($field->id, $result->id);
    }

    public function test_get_field_by_name_returns_null_when_not_found(): void
    {
        $form   = Form::factory()->create();
        $result = $this->repository->getFieldByName($form->id, 'nonexistent');

        $this->assertNull($result);
    }
}
