<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Services\FormExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Appsolutely\AIO\Tests\TestCase;

final class FormExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private FormExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('exports');
        $this->service = app(FormExportService::class);
    }

    // --- exportFormEntriesToCsv ---

    public function test_export_form_entries_to_csv_returns_file_path(): void
    {
        $form  = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        $path = $this->service->exportFormEntriesToCsv($form->id);

        $this->assertIsString($path);
    }

    public function test_export_form_entries_to_csv_without_spam(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
            'is_spam'      => false,
        ]);

        $path = $this->service->exportFormEntriesToCsv($form->id, false);

        $this->assertIsString($path);
    }

    public function test_export_form_entries_to_csv_with_metadata(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        $path = $this->service->exportFormEntriesToCsv($form->id, false, true);

        $this->assertIsString($path);
    }

    public function test_export_form_entries_to_csv_creates_file(): void
    {
        $form = Form::factory()->create(['slug' => 'export-test-form']);
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        $path = $this->service->exportFormEntriesToCsv($form->id);

        $this->assertNotEmpty($path);
    }

    // --- exportFormEntriesToExcel ---

    public function test_export_form_entries_to_excel_returns_file_path(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        $path = $this->service->exportFormEntriesToExcel($form->id);

        $this->assertIsString($path);
    }

    public function test_export_form_entries_to_excel_without_spam(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        $path = $this->service->exportFormEntriesToExcel($form->id, false);

        $this->assertIsString($path);
    }

    public function test_export_form_entries_to_excel_with_metadata(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        $path = $this->service->exportFormEntriesToExcel($form->id, false, true);

        $this->assertIsString($path);
    }
}
