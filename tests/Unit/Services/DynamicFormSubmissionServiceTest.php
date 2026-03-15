<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Exceptions\FormNotFoundException;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Services\DynamicFormSubmissionService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

final class DynamicFormSubmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicFormSubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicFormSubmissionService::class);
    }

    // --- submitForm ---

    public function test_submit_form_throws_form_not_found_exception_for_unknown_slug(): void
    {
        $this->expectException(FormNotFoundException::class);

        $this->service->submitForm('non-existent-form-slug', []);
    }

    public function test_submit_form_creates_form_entry(): void
    {
        $form = Form::factory()->create(['slug' => 'test-submit-form']);

        $entry = $this->service->submitForm('test-submit-form', [
            'first_name' => 'Alice',
            'last_name'  => 'Wonder',
            'email'      => 'alice@example.com',
        ]);

        $this->assertInstanceOf(FormEntry::class, $entry);
        $this->assertEquals($form->id, $entry->form_id);
        $this->assertEquals('Alice', $entry->first_name);
        $this->assertEquals('alice@example.com', $entry->email);
    }

    public function test_submit_form_stores_request_metadata_when_request_provided(): void
    {
        $form = Form::factory()->create(['slug' => 'meta-form']);

        $request = Request::create('/', 'POST');

        $entry = $this->service->submitForm('meta-form', [
            'email' => 'test@example.com',
        ], $request);

        $this->assertInstanceOf(FormEntry::class, $entry);
        $this->assertDatabaseHas('form_entries', ['form_id' => $form->id]);
    }

    public function test_submit_form_uses_fullname_fallback_for_name(): void
    {
        $form = Form::factory()->create(['slug' => 'name-fallback-form']);

        $entry = $this->service->submitForm('name-fallback-form', [
            'fullname' => 'Bob Builder',
        ]);

        $this->assertEquals('Bob Builder', $entry->name);
    }

    public function test_submit_form_uses_phone_fallback_for_mobile(): void
    {
        $form = Form::factory()->create(['slug' => 'phone-fallback-form']);

        $entry = $this->service->submitForm('phone-fallback-form', [
            'phone' => '0400123456',
        ]);

        $this->assertEquals('0400123456', $entry->mobile);
    }

    // --- Meta collection from third-party cookies ---

    public function test_submit_form_collects_meta_from_raw_cookies(): void
    {
        $form = Form::factory()->create([
            'slug'                 => 'cookie-meta-form',
            'meta_keys_to_collect' => ['utm_source', 'utm_medium'],
        ]);

        // Simulate third-party cookies via $_COOKIE (not encrypted by Laravel)
        $_COOKIE['utm_source'] = 'google';
        $_COOKIE['utm_medium'] = 'cpc';

        try {
            $request = Request::create('/', 'POST');

            $entry = $this->service->submitForm('cookie-meta-form', [
                'email' => 'test@example.com',
            ], $request);

            $this->assertEquals('google', $entry->getMetaValue('utm_source'));
            $this->assertEquals('cpc', $entry->getMetaValue('utm_medium'));
        } finally {
            unset($_COOKIE['utm_source'], $_COOKIE['utm_medium']);
        }
    }

    public function test_submit_form_decrypts_laravel_cookies_via_request(): void
    {
        $form = Form::factory()->create([
            'slug'                 => 'encrypted-cookie-form',
            'meta_keys_to_collect' => ['utm_source', 'gclid'],
        ]);

        // Simulate request with decrypted cookies (as EncryptCookies middleware would provide)
        // No $_COOKIE set — proves the value comes from $request->cookie(), not $_COOKIE
        $request = Request::create('/', 'POST', [], [
            'utm_source' => 'google',
            'gclid'      => 'abc123',
        ]);

        $entry = $this->service->submitForm('encrypted-cookie-form', [
            'email' => 'test@example.com',
        ], $request);

        $this->assertEquals('google', $entry->getMetaValue('utm_source'));
        $this->assertEquals('abc123', $entry->getMetaValue('gclid'));
    }

    public function test_submit_form_prefers_request_cookie_over_raw_cookie(): void
    {
        $form = Form::factory()->create([
            'slug'                 => 'prefer-request-cookie-form',
            'meta_keys_to_collect' => ['utm_source'],
        ]);

        // Request has decrypted value, $_COOKIE has encrypted gibberish
        $request = Request::create('/', 'POST', [], [
            'utm_source' => 'google',
        ]);
        $_COOKIE['utm_source'] = 'eyJpdiI6encrypted_gibberish';

        try {
            $entry = $this->service->submitForm('prefer-request-cookie-form', [
                'email' => 'test@example.com',
            ], $request);

            // Should use the decrypted request value, not the raw $_COOKIE
            $this->assertEquals('google', $entry->getMetaValue('utm_source'));
        } finally {
            unset($_COOKIE['utm_source']);
        }
    }

    public function test_submit_form_skips_missing_cookies(): void
    {
        $form = Form::factory()->create([
            'slug'                 => 'partial-cookie-form',
            'meta_keys_to_collect' => ['utm_source', 'utm_medium', 'gclid'],
        ]);

        $_COOKIE['utm_source'] = 'google';
        // utm_medium and gclid not set

        try {
            $request = Request::create('/', 'POST');

            $entry = $this->service->submitForm('partial-cookie-form', [
                'email' => 'test@example.com',
            ], $request);

            $this->assertEquals('google', $entry->getMetaValue('utm_source'));
            $this->assertNull($entry->getMetaValue('utm_medium'));
            $this->assertNull($entry->getMetaValue('gclid'));
        } finally {
            unset($_COOKIE['utm_source']);
        }
    }

    public function test_submit_form_skips_meta_when_no_keys_configured(): void
    {
        $form = Form::factory()->create([
            'slug'                 => 'no-meta-form',
            'meta_keys_to_collect' => null,
        ]);

        $request = Request::create('/', 'POST');

        $entry = $this->service->submitForm('no-meta-form', [
            'email' => 'test@example.com',
        ], $request);

        $this->assertNull($entry->meta);
    }

    public function test_submit_form_ignores_empty_cookie_values(): void
    {
        $form = Form::factory()->create([
            'slug'                 => 'empty-cookie-form',
            'meta_keys_to_collect' => ['utm_source', 'utm_medium'],
        ]);

        $_COOKIE['utm_source'] = 'google';
        $_COOKIE['utm_medium'] = '';

        try {
            $request = Request::create('/', 'POST');

            $entry = $this->service->submitForm('empty-cookie-form', [
                'email' => 'test@example.com',
            ], $request);

            $this->assertEquals('google', $entry->getMetaValue('utm_source'));
            $this->assertNull($entry->getMetaValue('utm_medium'));
        } finally {
            unset($_COOKIE['utm_source'], $_COOKIE['utm_medium']);
        }
    }
}
