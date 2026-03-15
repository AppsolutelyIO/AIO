<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Services\DynamicFormExportService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DynamicFormExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicFormExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicFormExportService::class);
    }

    // --- exportFormEntries ---

    public function test_export_form_entries_returns_csv_string(): void
    {
        $form  = Form::factory()->create();
        $entry = FormEntry::factory()->create([
            'form_id'      => $form->id,
            'first_name'   => 'Jane',
            'last_name'    => 'Doe',
            'email'        => 'jane@example.com',
            'submitted_at' => now(),
        ]);

        $csv = $this->service->exportFormEntries($form->id);

        $this->assertIsString($csv);
        $this->assertStringContainsString('ID', $csv);
        $this->assertStringContainsString('Submitted At', $csv);
    }

    public function test_export_form_entries_includes_entry_data(): void
    {
        $form  = Form::factory()->create();
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'first_name'   => 'John',
            'last_name'    => 'Smith',
            'email'        => 'john@example.com',
            'submitted_at' => now(),
        ]);

        $csv = $this->service->exportFormEntries($form->id);

        $this->assertStringContainsString('John', $csv);
        $this->assertStringContainsString('Smith', $csv);
        $this->assertStringContainsString('john@example.com', $csv);
    }

    public function test_export_form_entries_returns_csv_with_header_only_when_no_entries(): void
    {
        $form = Form::factory()->create();

        $csv = $this->service->exportFormEntries($form->id);

        $this->assertIsString($csv);
        $lines = array_filter(explode("\n", trim($csv)));
        // Should have only header row
        $this->assertCount(1, $lines);
    }

    // --- exportFormEntriesForApi ---

    public function test_export_form_entries_for_api_returns_streamed_response(): void
    {
        $form = Form::factory()->create();

        $response = $this->service->exportFormEntriesForApi($form->id);

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_export_form_entries_for_api_with_null_form_id_returns_streamed_response(): void
    {
        $response = $this->service->exportFormEntriesForApi(null);

        $this->assertInstanceOf(StreamedResponse::class, $response);
    }
}
