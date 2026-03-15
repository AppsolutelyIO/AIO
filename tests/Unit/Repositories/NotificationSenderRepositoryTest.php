<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NotificationSenderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NotificationSenderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(NotificationSenderRepository::class);
    }

    private function createSender(array $attrs = []): NotificationSender
    {
        return NotificationSender::create(array_merge([
            'name'         => 'Test Sender',
            'slug'         => 'test-sender-' . uniqid(),
            'type'         => 'smtp',
            'from_address' => 'from@example.com',
            'category'     => 'internal',
            'is_default'   => false,
            'is_active'    => true,
            'priority'     => 0,
        ], $attrs));
    }

    // --- getDefaultForCategory ---

    public function test_get_default_for_category_returns_default_active_sender(): void
    {
        $this->createSender(['category' => 'internal', 'is_default' => true, 'is_active' => true]);

        $result = $this->repository->getDefaultForCategory('internal');

        $this->assertInstanceOf(NotificationSender::class, $result);
        $this->assertTrue($result->is_default);
        $this->assertTrue($result->is_active);
    }

    public function test_get_default_for_category_returns_null_when_none(): void
    {
        $this->createSender(['category' => 'internal', 'is_default' => false]);

        $result = $this->repository->getDefaultForCategory('internal');

        $this->assertNull($result);
    }

    public function test_get_default_for_category_ignores_inactive(): void
    {
        $this->createSender(['category' => 'external', 'is_default' => true, 'is_active' => false]);

        $result = $this->repository->getDefaultForCategory('external');

        $this->assertNull($result);
    }

    public function test_get_default_for_category_ignores_other_categories(): void
    {
        $this->createSender(['category' => 'system', 'is_default' => true, 'is_active' => true]);

        $result = $this->repository->getDefaultForCategory('external');

        $this->assertNull($result);
    }

    public function test_get_default_for_category_returns_highest_priority(): void
    {
        $this->createSender(['category' => 'internal', 'is_default' => true, 'is_active' => true, 'priority' => 10, 'name' => 'High']);
        $this->createSender(['category' => 'internal', 'is_default' => true, 'is_active' => true, 'priority' => 5, 'name' => 'Low']);

        $result = $this->repository->getDefaultForCategory('internal');

        $this->assertEquals('High', $result->name);
    }

    // --- getActiveByCategory ---

    public function test_get_active_by_category_returns_only_active_for_category(): void
    {
        $this->createSender(['category' => 'internal', 'is_active' => true]);
        $this->createSender(['category' => 'internal', 'is_active' => true]);
        $this->createSender(['category' => 'internal', 'is_active' => false]);
        $this->createSender(['category' => 'external', 'is_active' => true]);

        $result = $this->repository->getActiveByCategory('internal');

        $this->assertCount(2, $result);
        $result->each(fn ($s) => $this->assertEquals('internal', $s->category));
        $result->each(fn ($s) => $this->assertTrue($s->is_active));
    }

    public function test_get_active_by_category_returns_empty_when_none(): void
    {
        $result = $this->repository->getActiveByCategory('system');

        $this->assertCount(0, $result);
    }

    // --- findBySlug ---

    public function test_find_by_slug_returns_sender(): void
    {
        $this->createSender(['slug' => 'my-unique-slug']);

        $result = $this->repository->findBySlug('my-unique-slug');

        $this->assertInstanceOf(NotificationSender::class, $result);
        $this->assertEquals('my-unique-slug', $result->slug);
    }

    public function test_find_by_slug_returns_null_when_not_found(): void
    {
        $result = $this->repository->findBySlug('nonexistent-slug');

        $this->assertNull($result);
    }

    // --- getActive ---

    public function test_get_active_returns_only_active_senders(): void
    {
        $this->createSender(['is_active' => true]);
        $this->createSender(['is_active' => true]);
        $this->createSender(['is_active' => false]);

        $result = $this->repository->getActive();

        $this->assertCount(2, $result);
        $result->each(fn ($s) => $this->assertTrue($s->is_active));
    }

    // --- getByType ---

    public function test_get_by_type_returns_active_senders_of_type(): void
    {
        $this->createSender(['type' => 'smtp', 'is_active' => true]);
        $this->createSender(['type' => 'smtp', 'is_active' => true]);
        $this->createSender(['type' => 'log', 'is_active' => true]);
        $this->createSender(['type' => 'smtp', 'is_active' => false]);

        $result = $this->repository->getByType('smtp');

        $this->assertCount(2, $result);
        $result->each(fn ($s) => $this->assertEquals('smtp', $s->type));
    }

    public function test_get_by_type_returns_empty_when_none(): void
    {
        $result = $this->repository->getByType('mailgun');

        $this->assertCount(0, $result);
    }
}
