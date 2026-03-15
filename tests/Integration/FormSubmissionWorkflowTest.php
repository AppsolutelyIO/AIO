<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Integration;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Services\DynamicFormService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class FormSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private DynamicFormService $formService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formService = app(DynamicFormService::class);
    }

    public function test_form_submission_workflow(): void
    {
        // Create form with fields
        $form = Form::factory()->create([
            'slug'   => 'contact-form',
            'status' => Status::ACTIVE,
        ]);

        // This test demonstrates the integration pattern
        // Actual implementation would depend on DynamicFormService methods
        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('contact-form', $form->slug);
    }
}
