<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Services\FormEntriesPullService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class FormEntriesPullServiceTest extends TestCase
{
    use RefreshDatabase;

    private FormEntriesPullService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FormEntriesPullService::class);
    }

    // --- pullEntries ---

    public function test_pull_entries_returns_false_and_404_when_form_not_found(): void
    {
        [$success, $result, $error] = $this->service->pullEntries('non-existent-slug', 'some-token', []);

        $this->assertFalse($success);
        $this->assertEquals(404, $result);
        $this->assertNull($error);
    }

    public function test_pull_entries_returns_false_and_204_when_form_has_no_api_token(): void
    {
        $form = Form::factory()->create(['slug' => 'no-token-form', 'api_access_token' => null]);

        [$success, $result, $error] = $this->service->pullEntries('no-token-form', 'some-token', []);

        $this->assertFalse($success);
        $this->assertEquals(204, $result);
    }

    public function test_pull_entries_returns_false_and_401_when_token_does_not_match(): void
    {
        $form = Form::factory()->create([
            'slug'             => 'token-form',
            'api_access_token' => 'correct-token',
        ]);

        [$success, $result, $error] = $this->service->pullEntries('token-form', 'wrong-token', []);

        $this->assertFalse($success);
        $this->assertEquals(401, $result);
    }

    public function test_pull_entries_returns_false_and_204_when_token_is_null(): void
    {
        $form = Form::factory()->create([
            'slug'             => 'nullable-token-form',
            'api_access_token' => 'some-token',
        ]);

        [$success, $result, $error] = $this->service->pullEntries('nullable-token-form', null, []);

        $this->assertFalse($success);
        $this->assertEquals(204, $result);
    }

    public function test_pull_entries_returns_paginator_on_success(): void
    {
        $token = 'valid-api-token';
        $form  = Form::factory()->create([
            'slug'             => 'valid-form',
            'api_access_token' => $token,
        ]);

        FormEntry::factory()->count(3)->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        [$success, $paginator, $error] = $this->service->pullEntries('valid-form', $token, ['form_slug' => 'valid-form']);

        $this->assertTrue($success);
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertNull($error);
    }

    public function test_pull_entries_api_array_contains_expected_keys(): void
    {
        $token = 'api-key-xyz';
        $form  = Form::factory()->create([
            'slug'             => 'keys-form',
            'api_access_token' => $token,
        ]);

        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        [$success, $paginator] = $this->service->pullEntries('keys-form', $token, ['form_slug' => 'keys-form']);

        $this->assertTrue($success);
        $entry = $paginator->items()[0];

        $this->assertArrayHasKey('id', $entry);
        $this->assertArrayHasKey('form_id', $entry);
        $this->assertArrayHasKey('submitted_at', $entry);
        $this->assertArrayHasKey('email', $entry);
        $this->assertArrayHasKey('data', $entry);
        $this->assertArrayHasKey('meta', $entry);
    }
}
