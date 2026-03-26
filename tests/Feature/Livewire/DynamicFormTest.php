<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature\Livewire;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Livewire\DynamicForm;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Appsolutely\AIO\Services\Contracts\TurnstileServiceInterface;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

final class DynamicFormTest extends TestCase
{
    use RefreshDatabase;

    private Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = Form::factory()->create(['slug' => 'contact']);

        FormField::factory()->create([
            'form_id'  => $this->form->id,
            'label'    => 'Name',
            'name'     => 'name',
            'type'     => FormFieldType::Text->value,
            'required' => true,
            'sort'     => 1,
        ]);

        FormField::factory()->create([
            'form_id'  => $this->form->id,
            'label'    => 'Email',
            'name'     => 'email',
            'type'     => FormFieldType::Email->value,
            'required' => true,
            'sort'     => 2,
        ]);

        // Minimal view stub so Livewire can render without the full theme system
        $this->registerStubView();

        // Mock ManifestService to avoid theme dependency
        $this->app->instance(ManifestServiceInterface::class, new class() implements ManifestServiceInterface
        {
            public function getDisplayOptions(string $blockReference, ?string $themeName = null): array
            {
                return [];
            }

            public function getTemplateConfig(string $blockReference, ?string $themeName = null): ?array
            {
                return null;
            }

            public function loadManifest(?string $themeName = null): array
            {
                return [];
            }

            public function clearCache(?string $themeName = null): void {}
        });

        // Default: honeypot enabled, turnstile disabled
        config([
            'forms.captcha.honeypot.enabled'     => true,
            'forms.captcha.honeypot.min_time'    => 3,
            'forms.captcha.turnstile.enabled'    => false,
            'forms.captcha.turnstile.site_key'   => '',
            'forms.captcha.turnstile.secret_key' => '',
        ]);
    }

    // ---------------------------------------------------------------
    //  Honeypot: hidden field filled
    // ---------------------------------------------------------------

    public function test_honeypot_triggers_fake_success_when_hidden_field_filled(): void
    {
        $component = $this->mountForm();

        $component
            ->set('website', 'i-am-a-bot')
            ->set('formData.name', 'Bot')
            ->set('formData.email', 'bot@example.com')
            ->call('submit')
            ->assertSet('submitted', true);

        // No real entry saved
        $this->assertDatabaseMissing('form_entries', ['email' => 'bot@example.com']);
    }

    // ---------------------------------------------------------------
    //  Honeypot: timing check is skipped across Livewire requests
    // ---------------------------------------------------------------

    public function test_honeypot_timing_check_skipped_when_mounted_at_is_zero(): void
    {
        // mountedAt is a protected property — Livewire doesn't persist it
        // across requests, so it's always 0 on subsequent calls.
        // The timing check requires mountedAt > 0, so it's effectively skipped.
        config(['forms.captcha.honeypot.min_time' => 9999]);

        $component = $this->mountForm();

        $component
            ->set('formData.name', 'Speed User')
            ->set('formData.email', 'speed@example.com')
            ->call('submit')
            ->assertSet('submitted', true);

        // Entry IS saved because timing check can't trigger (mountedAt = 0)
        $this->assertDatabaseHas('form_entries', ['email' => 'speed@example.com']);
    }

    // ---------------------------------------------------------------
    //  Honeypot disabled: timing check skipped
    // ---------------------------------------------------------------

    public function test_honeypot_disabled_skips_all_honeypot_checks(): void
    {
        config(['forms.captcha.honeypot.enabled' => false]);

        $component = $this->mountForm();

        // Even with honeypot field filled + immediate submission, it should proceed
        $component
            ->set('website', 'filled-but-ignored')
            ->set('formData.name', 'Real User')
            ->set('formData.email', 'real@example.com')
            ->call('submit')
            ->assertSet('submitted', true);

        // Real entry IS saved because honeypot was disabled
        $this->assertDatabaseHas('form_entries', ['email' => 'real@example.com']);
    }

    // ---------------------------------------------------------------
    //  Turnstile: verification failure
    // ---------------------------------------------------------------

    public function test_turnstile_failure_throws_validation_exception(): void
    {
        $this->enableTurnstile(verifyResult: false);

        // Bypass honeypot timing by setting mountedAt far in the past
        $component = $this->mountForm();

        $component
            ->set('formData.name', 'User')
            ->set('formData.email', 'user@example.com')
            ->set('turnstileToken', 'invalid-token')
            ->call('submit')
            ->assertHasErrors('turnstile');
    }

    // ---------------------------------------------------------------
    //  Turnstile: verification success
    // ---------------------------------------------------------------

    public function test_turnstile_success_allows_submission(): void
    {
        $this->enableTurnstile(verifyResult: true);

        $component = $this->mountForm();

        $component
            ->set('formData.name', 'Valid User')
            ->set('formData.email', 'valid@example.com')
            ->set('turnstileToken', 'valid-token')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('form_entries', ['email' => 'valid@example.com']);
    }

    // ---------------------------------------------------------------
    //  Turnstile disabled: skips verification
    // ---------------------------------------------------------------

    public function test_turnstile_disabled_skips_verification(): void
    {
        config(['forms.captcha.turnstile.enabled' => false]);

        // Also disable honeypot timing
        config(['forms.captcha.honeypot.min_time' => 0]);

        $component = $this->mountForm();

        $component
            ->set('formData.name', 'No Captcha')
            ->set('formData.email', 'nocaptcha@example.com')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('form_entries', ['email' => 'nocaptcha@example.com']);
    }

    // ---------------------------------------------------------------
    //  Rate limiting
    // ---------------------------------------------------------------

    public function test_rate_limiting_blocks_after_five_submissions(): void
    {
        config(['forms.captcha.honeypot.min_time' => 0]);

        // Simulate 5 prior hits
        $key = 'form-submission:127.0.0.1';
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        $component = $this->mountForm();

        $component
            ->set('formData.name', 'Rate Limited')
            ->set('formData.email', 'limited@example.com')
            ->call('submit')
            ->assertHasErrors('form');

        $this->assertDatabaseMissing('form_entries', ['email' => 'limited@example.com']);
    }

    // ---------------------------------------------------------------
    //  Normal successful submission
    // ---------------------------------------------------------------

    public function test_successful_submission_creates_entry(): void
    {
        config(['forms.captcha.honeypot.min_time' => 0]);

        $component = $this->mountForm();

        $component
            ->set('formData.name', 'Jane Doe')
            ->set('formData.email', 'jane@example.com')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('form_entries', [
            'form_id' => $this->form->id,
            'email'   => 'jane@example.com',
        ]);
    }

    // ---------------------------------------------------------------
    //  Reset form clears state
    // ---------------------------------------------------------------

    public function test_reset_form_clears_submission_state(): void
    {
        config(['forms.captcha.honeypot.min_time' => 0]);

        $component = $this->mountForm();

        $component
            ->set('formData.name', 'Reset Test')
            ->set('formData.email', 'reset@example.com')
            ->call('submit')
            ->assertSet('submitted', true)
            ->call('resetForm')
            ->assertSet('submitted', false)
            ->assertSet('formData', [])
            ->assertSet('turnstileToken', '');
    }

    // ---------------------------------------------------------------
    //  Honeypot + Turnstile combined: honeypot takes priority
    // ---------------------------------------------------------------

    public function test_honeypot_takes_priority_over_turnstile(): void
    {
        $this->enableTurnstile(verifyResult: false);

        $component = $this->mountForm();

        // Honeypot filled + Turnstile would fail, but honeypot triggers first → fake success
        $component
            ->set('website', 'bot-value')
            ->set('formData.name', 'Bot')
            ->set('formData.email', 'bot2@example.com')
            ->set('turnstileToken', 'bad-token')
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors(); // No Turnstile error because honeypot short-circuited

        $this->assertDatabaseMissing('form_entries', ['email' => 'bot2@example.com']);
    }

    // ===============================================================
    //  Helpers
    // ===============================================================

    private function mountForm(): Testable
    {
        return Livewire::test(DynamicForm::class, [
            'page' => [
                'slug'       => 'contact-page',
                'page_alias' => null,
            ],
            'viewName'       => 'dynamic-form',
            'queryOptions'   => ['form_slug' => 'contact'],
            'displayOptions' => [
                'success_message'       => 'Thank you!',
                'redirect'              => '',
                'redirect_url'          => '',
                'redirect_after_submit' => '',
            ],
        ]);
    }

    private function enableTurnstile(bool $verifyResult): void
    {
        config([
            'forms.captcha.turnstile.enabled'    => true,
            'forms.captcha.turnstile.site_key'   => 'test-site-key',
            'forms.captcha.turnstile.secret_key' => 'test-secret-key',
            'forms.captcha.honeypot.min_time'    => 0, // Skip timing for turnstile-focused tests
        ]);

        $mock = \Mockery::mock(TurnstileServiceInterface::class);
        $mock->shouldReceive('isEnabled')->andReturn(true);
        $mock->shouldReceive('verify')->andReturn($verifyResult);

        $this->app->instance(TurnstileServiceInterface::class, $mock);
    }

    private function registerStubView(): void
    {
        // Register a minimal inline view so the component can render
        $stubPath = sys_get_temp_dir() . '/aio-test-views/livewire';
        if (! is_dir($stubPath)) {
            mkdir($stubPath, 0755, true);
        }

        $stubContent = '<div>stub form view</div>';
        file_put_contents($stubPath . '/dynamic-form.blade.php', $stubContent);

        // Also register themed_view fallback — add the temp path as a view namespace
        view()->addLocation(sys_get_temp_dir() . '/aio-test-views');
    }
}
