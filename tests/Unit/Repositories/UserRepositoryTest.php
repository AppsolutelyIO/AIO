<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use App\Models\User;
use Appsolutely\AIO\Repositories\UserRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
    }

    public function test_search_returns_users_matching_name(): void
    {
        $user1 = User::factory()->create(['name' => 'John Doe']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);
        User::factory()->create(['name' => 'Bob Wilson']);

        $result = $this->repository->search('John');

        $this->assertCount(1, $result->get());
        $this->assertEquals($user1->id, $result->first()->id);
    }

    public function test_search_returns_users_matching_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        User::factory()->create(['email' => 'other@example.com']);

        $result = $this->repository->search('test@example.com');

        $this->assertCount(1, $result->get());
        $this->assertEquals($user->id, $result->first()->id);
    }

    public function test_search_returns_empty_when_no_matches(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);

        $result = $this->repository->search('nonexistent');

        $this->assertCount(0, $result->get());
    }

    public function test_find_by_email_returns_user(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $result = $this->repository->findByEmail('test@example.com');

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($result);
    }

    public function test_get_active_users_returns_only_verified_users(): void
    {
        $active1 = User::factory()->create(['email_verified_at' => now()]);
        $active2 = User::factory()->create(['email_verified_at' => now()->subDay()]);
        User::factory()->create(['email_verified_at' => null]);

        $result = $this->repository->getActiveUsers();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $active1->id));
        $this->assertTrue($result->contains('id', $active2->id));
    }

    public function test_get_paginated_returns_paginated_users(): void
    {
        User::factory()->count(25)->create();

        $result = $this->repository->getPaginated(10);

        $this->assertEquals(25, $result->total());
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(3, $result->lastPage());
    }

    public function test_get_paginated_orders_by_created_at_desc(): void
    {
        $oldest = User::factory()->create(['created_at' => now()->subDays(5)]);
        $newest = User::factory()->create(['created_at' => now()]);
        $middle = User::factory()->create(['created_at' => now()->subDays(2)]);

        $result = $this->repository->getPaginated(10);

        $this->assertEquals($newest->id, $result->first()->id);
        $this->assertEquals($middle->id, $result->get(1)->id);
        $this->assertEquals($oldest->id, $result->last()->id);
    }
}
