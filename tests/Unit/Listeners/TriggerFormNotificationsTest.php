<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Listeners;

use Appsolutely\AIO\Events\FormSubmitted;
use Appsolutely\AIO\Listeners\TriggerFormNotifications;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\Contracts\NotificationServiceInterface;
use Appsolutely\AIO\Services\FormFieldFormatterService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;

final class TriggerFormNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_listener_triggers_notification_service(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form-trigger']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'data'    => json_encode(['name' => 'John Doe']),
        ]);

        $mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $mockNotificationService->shouldReceive('trigger')
            ->once()
            ->with(
                'form_submission',
                'test-form-trigger',
                Mockery::on(function ($data) use ($entry, $form) {
                    return isset($data['entry_id'])
                        && $data['entry_id']  === $entry->id
                        && $data['form_id']   === $form->id
                        && $data['form_name'] === $form->name
                        && $data['user_name'] === 'John Doe'
                        && isset($data['form_fields_html'])
                        && isset($data['form_fields_text']);
                })
            );

        $formatter = new FormFieldFormatterService();
        $listener  = new TriggerFormNotifications($mockNotificationService, $formatter);

        $event = new FormSubmitted($form, $entry, []);

        $listener->handle($event);

        // Mockery verifies shouldReceive('trigger')->once() was called
        $mockNotificationService->shouldHaveReceived('trigger')->once();
        $this->addToAssertionCount(1);
    }

    public function test_listener_respects_cache_lock(): void
    {
        $form  = Form::factory()->create(['slug' => 'test-form-lock']);
        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        // Set a lock using the same key format as the listener
        $lockKey = 'form_notification_processing:' . $entry->id . '_' . $form->id;
        Cache::lock($lockKey, 60)->get();

        $mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $mockNotificationService->shouldNotReceive('trigger');

        $formatter = new FormFieldFormatterService();
        $listener  = new TriggerFormNotifications($mockNotificationService, $formatter);

        $event = new FormSubmitted($form, $entry, []);

        $listener->handle($event);

        // Clean up
        Cache::lock($lockKey)->release();

        // Mockery verifies shouldNotReceive('trigger') was respected
        $mockNotificationService->shouldNotHaveReceived('trigger');
        $this->addToAssertionCount(1);
    }

    public function test_listener_loads_form_fields_if_not_loaded(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form-fields']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'data'    => json_encode(['name' => 'John Doe']),
        ]);

        // Ensure fields are not loaded
        $form = Form::find($form->id);
        $this->assertFalse($form->relationLoaded('fields'));

        $mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $mockNotificationService->shouldReceive('trigger')
            ->once()
            ->with('form_submission', 'test-form-fields', Mockery::any());

        $formatter = new FormFieldFormatterService();
        $listener  = new TriggerFormNotifications($mockNotificationService, $formatter);

        $event = new FormSubmitted($form, $entry, []);

        $listener->handle($event);

        // Fields should now be loaded
        $this->assertTrue($form->relationLoaded('fields'));
    }

    public function test_listener_includes_entry_and_form_ids_in_notification_data(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form-ids', 'name' => 'Contact Form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'Jane Doe',
            'data'    => json_encode(['name' => 'Jane Doe']),
        ]);

        $capturedData = null;

        $mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $mockNotificationService->shouldReceive('trigger')
            ->once()
            ->with('form_submission', 'test-form-ids', Mockery::capture($capturedData));

        $formatter = new FormFieldFormatterService();
        $listener  = new TriggerFormNotifications($mockNotificationService, $formatter);

        $event = new FormSubmitted($form, $entry, []);

        $listener->handle($event);

        $this->assertIsArray($capturedData);
        $this->assertArrayHasKey('entry_id', $capturedData);
        $this->assertArrayHasKey('form_id', $capturedData);
        $this->assertEquals($entry->id, $capturedData['entry_id']);
        $this->assertEquals($form->id, $capturedData['form_id']);
        $this->assertEquals('Contact Form', $capturedData['form_name']);
        $this->assertEquals('Jane Doe', $capturedData['user_name']);
    }

    public function test_listener_uses_formatter_service_for_data_preparation(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form-formatter']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'Test User',
            'email'   => 'test@example.com',
            'data'    => json_encode(['name' => 'Test User']),
        ]);

        // Use the real formatter service
        $formatter = new FormFieldFormatterService();

        $mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $mockNotificationService->shouldReceive('trigger')
            ->once()
            ->with(
                'form_submission',
                'test-form-formatter',
                Mockery::on(function ($data) use ($entry, $form) {
                    // Verify the data structure created by the formatter service
                    return isset($data['entry_id'])
                        && $data['entry_id']  === $entry->id
                        && $data['form_id']   === $form->id
                        && $data['form_name'] === $form->name
                        && $data['user_name'] === 'Test User'
                        && array_key_exists('form_fields_html', $data)
                        && array_key_exists('form_fields_text', $data);
                })
            );

        $listener = new TriggerFormNotifications($mockNotificationService, $formatter);

        $event = new FormSubmitted($form, $entry, []);

        $listener->handle($event);

        // Mockery verifies shouldReceive('trigger')->once() was called with correct data
        $mockNotificationService->shouldHaveReceived('trigger')->once();
        $this->addToAssertionCount(1);
    }

    public function test_listener_releases_lock_after_processing(): void
    {
        $form = Form::factory()->create(['slug' => 'test-form-release']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'data'    => json_encode(['name' => 'Test']),
        ]);

        $mockNotificationService = Mockery::mock(NotificationServiceInterface::class);
        $mockNotificationService->shouldReceive('trigger')->once();

        $formatter = new FormFieldFormatterService();
        $listener  = new TriggerFormNotifications($mockNotificationService, $formatter);

        $event = new FormSubmitted($form, $entry, []);

        $listener->handle($event);

        // Lock should be released - we should be able to acquire it
        $lockKey = 'form_notification_processing:' . $entry->id . '_' . $form->id;
        $lock    = Cache::lock($lockKey, 60);
        $this->assertTrue($lock->get());
        $lock->release();
    }
}
