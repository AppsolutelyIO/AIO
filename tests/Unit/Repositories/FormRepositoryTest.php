<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class FormRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FormRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(FormRepository::class);
    }

    public function test_find_by_slug_returns_active_form_with_fields(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form', 'status' => Status::ACTIVE]);
        FormField::factory()->create(['form_id' => $form->id, 'sort' => 1]);
        FormField::factory()->create(['form_id' => $form->id, 'sort' => 2]);

        $result = $this->repository->findBySlug('contact-form');

        $this->assertInstanceOf(Form::class, $result);
        $this->assertEquals($form->id, $result->id);
        $this->assertTrue($result->relationLoaded('fields'));
        $this->assertCount(2, $result->fields);
    }

    public function test_find_by_slug_returns_null_for_inactive_form(): void
    {
        Form::factory()->create(['slug' => 'contact-form', 'status' => Status::INACTIVE]);

        $result = $this->repository->findBySlug('contact-form');

        $this->assertNull($result);
    }

    public function test_get_active_forms_with_fields_returns_only_active_forms(): void
    {
        $active1 = Form::factory()->create(['status' => Status::ACTIVE, 'name' => 'Form A']);
        $active2 = Form::factory()->create(['status' => Status::ACTIVE, 'name' => 'Form B']);
        Form::factory()->create(['status' => Status::INACTIVE]);

        FormField::factory()->create(['form_id' => $active1->id, 'sort' => 1]);
        FormField::factory()->create(['form_id' => $active2->id, 'sort' => 1]);

        $result = $this->repository->getActiveFormsWithFields();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $active1->id));
        $this->assertTrue($result->contains('id', $active2->id));
    }

    public function test_get_form_with_stats_includes_entry_counts(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id]);

        $result = $this->repository->getFormWithStats($form->id);

        $this->assertInstanceOf(Form::class, $result);
        $this->assertArrayHasKey('entries_count', $result->getAttributes());
        $this->assertArrayHasKey('valid_entries_count', $result->getAttributes());
    }

    public function test_create_with_fields_creates_form_and_fields(): void
    {
        $formData   = ['name' => 'Test Form', 'slug' => 'test-form', 'status' => Status::ACTIVE];
        $fieldsData = [
            ['label' => 'Name', 'name' => 'name', 'type' => 'text', 'sort' => 1],
            ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'sort' => 2],
        ];

        $result = $this->repository->createWithFields($formData, $fieldsData);

        $this->assertInstanceOf(Form::class, $result);
        $this->assertEquals('Test Form', $result->name);
        $this->assertCount(2, $result->fields);
        $this->assertTrue($result->relationLoaded('fields'));
    }

    public function test_update_with_fields_updates_form_and_syncs_fields(): void
    {
        $form   = Form::factory()->create();
        $field1 = FormField::factory()->create(['form_id' => $form->id]);
        $field2 = FormField::factory()->create(['form_id' => $form->id]);

        $formData   = ['name' => 'Updated Form'];
        $fieldsData = [
            ['id' => $field1->id, 'label' => 'Updated Field 1', 'name' => 'updated_field_1', 'type' => 'text', 'sort' => 1],
            ['label' => 'New Field', 'name' => 'new_field', 'type' => 'text', 'sort' => 2],
        ];

        $result = $this->repository->updateWithFields($form->id, $formData, $fieldsData);

        $this->assertEquals('Updated Form', $result->name);
        $this->assertCount(2, $result->fields);
        $this->assertDatabaseMissing('form_fields', ['id' => $field2->id]);
    }

    public function test_count_by_status_returns_correct_count(): void
    {
        Form::factory()->count(3)->create(['status' => Status::ACTIVE]);
        Form::factory()->count(2)->create(['status' => Status::INACTIVE]);

        $this->assertEquals(3, $this->repository->countByStatus(Status::ACTIVE->value));
        $this->assertEquals(2, $this->repository->countByStatus(Status::INACTIVE->value));
    }

    public function test_find_by_slug_for_api_returns_form_regardless_of_status(): void
    {
        $inactive = Form::factory()->create(['slug' => 'inactive-form', 'status' => Status::INACTIVE]);

        $result = $this->repository->findBySlugForApi('inactive-form');

        $this->assertInstanceOf(Form::class, $result);
        $this->assertEquals($inactive->id, $result->id);
    }

    public function test_find_by_slug_for_api_returns_null_for_nonexistent(): void
    {
        $result = $this->repository->findBySlugForApi('nonexistent');

        $this->assertNull($result);
    }

    public function test_get_forms_with_stats_returns_all_forms_with_counts(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->count(3)->create(['form_id' => $form1->id]);
        FormEntry::factory()->count(2)->create(['form_id' => $form2->id]);

        $result = $this->repository->getFormsWithStats();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('entries_count', $result->first()->getAttributes());
        $this->assertArrayHasKey('valid_entries_count', $result->first()->getAttributes());
    }

    public function test_get_form_options_returns_name_keyed_by_id(): void
    {
        $form1 = Form::factory()->create(['name' => 'Contact Form']);
        $form2 = Form::factory()->create(['name' => 'Newsletter Form']);

        $result = $this->repository->getFormOptions();

        $this->assertIsArray($result);
        $this->assertEquals('Contact Form', $result[$form1->id]);
        $this->assertEquals('Newsletter Form', $result[$form2->id]);
    }
}
