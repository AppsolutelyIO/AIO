<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class NotificationTemplateRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NotificationTemplateRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(NotificationTemplateRepository::class);
    }

    // --- findBySlug ---

    public function test_find_by_slug_returns_active_template(): void
    {
        $template = NotificationTemplate::factory()->create([
            'slug'   => 'welcome-email',
            'status' => Status::ACTIVE,
        ]);

        $result = $this->repository->findBySlug('welcome-email');

        $this->assertInstanceOf(NotificationTemplate::class, $result);
        $this->assertEquals($template->id, $result->id);
    }

    public function test_find_by_slug_returns_null_for_inactive_template(): void
    {
        NotificationTemplate::factory()->create([
            'slug'   => 'inactive-tpl',
            'status' => Status::INACTIVE,
        ]);

        $this->assertNull($this->repository->findBySlug('inactive-tpl'));
    }

    public function test_find_by_slug_returns_null_for_non_existent(): void
    {
        $this->assertNull($this->repository->findBySlug('does-not-exist'));
    }

    // --- getByCategory ---

    public function test_get_by_category_returns_matching_templates(): void
    {
        NotificationTemplate::factory()->create(['category' => 'form', 'status' => Status::ACTIVE]);
        NotificationTemplate::factory()->create(['category' => 'form', 'status' => Status::ACTIVE]);
        NotificationTemplate::factory()->create(['category' => 'user', 'status' => Status::ACTIVE]);

        $result = $this->repository->getByCategory('form');

        $this->assertCount(2, $result);
        $result->each(fn ($t) => $this->assertEquals('form', $t->category));
    }

    public function test_get_by_category_excludes_inactive(): void
    {
        NotificationTemplate::factory()->create(['category' => 'form', 'status' => Status::ACTIVE]);
        NotificationTemplate::factory()->create(['category' => 'form', 'status' => Status::INACTIVE]);

        $result = $this->repository->getByCategory('form');

        $this->assertCount(1, $result);
    }

    // --- getActive ---

    public function test_get_active_returns_only_active_templates(): void
    {
        NotificationTemplate::factory()->create(['status' => Status::ACTIVE]);
        NotificationTemplate::factory()->create(['status' => Status::ACTIVE]);
        NotificationTemplate::factory()->create(['status' => Status::INACTIVE]);

        $result = $this->repository->getActive();

        $this->assertCount(2, $result);
        $result->each(fn ($t) => $this->assertEquals(Status::ACTIVE, $t->status));
    }

    // --- duplicate ---

    public function test_duplicate_creates_new_record(): void
    {
        $template = NotificationTemplate::factory()->create(['name' => 'Original']);

        $copy = $this->repository->duplicate($template->id);

        $this->assertNotEquals($template->id, $copy->id);
        $this->assertDatabaseCount('notification_templates', 2);
    }

    public function test_duplicate_sets_is_system_false(): void
    {
        $template = NotificationTemplate::factory()->create(['is_system' => true]);

        $copy = $this->repository->duplicate($template->id);

        $this->assertFalse($copy->is_system);
    }

    public function test_duplicate_appends_copy_to_name(): void
    {
        $template = NotificationTemplate::factory()->create(['name' => 'Welcome Email']);

        $copy = $this->repository->duplicate($template->id);

        $this->assertStringContainsString('Welcome Email', $copy->name);
        $this->assertStringContainsString('Copy', $copy->name);
    }

    // --- createWithUniqueSlug ---

    public function test_create_with_unique_slug_generates_slug_from_name(): void
    {
        $template = $this->repository->createWithUniqueSlug([
            'name'      => 'Welcome Email',
            'category'  => 'user',
            'subject'   => 'Welcome',
            'body_html' => '<p>Welcome</p>',
            'body_text' => 'Welcome',
        ]);

        $this->assertEquals('welcome-email', $template->slug);
    }

    public function test_create_with_unique_slug_handles_duplicate_slugs(): void
    {
        NotificationTemplate::factory()->create(['slug' => 'test-template']);

        $template = $this->repository->createWithUniqueSlug([
            'name'      => 'Test Template',
            'category'  => 'form',
            'subject'   => 'Test',
            'body_html' => '<p>Test</p>',
            'body_text' => 'Test',
        ]);

        $this->assertNotEquals('test-template', $template->slug);
        $this->assertStringStartsWith('test-template', $template->slug);
    }
}
