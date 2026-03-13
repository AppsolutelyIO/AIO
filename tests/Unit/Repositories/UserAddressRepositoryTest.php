<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use App\Models\User;
use Appsolutely\AIO\Models\UserAddress;
use Appsolutely\AIO\Repositories\UserAddressRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class UserAddressRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserAddressRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserAddressRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(UserAddressRepository::class, $this->repository);
    }

    public function test_model_returns_user_address_class(): void
    {
        $this->assertEquals(UserAddress::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_user_address(): void
    {
        $user = User::factory()->create();

        $address = $this->repository->create([
            'user_id'  => $user->id,
            'name'     => 'Home',
            'address'  => '123 Main St',
            'city'     => 'Sydney',
            'postcode' => '2000',
            'country'  => 'AU',
        ]);

        $this->assertInstanceOf(UserAddress::class, $address);
        $this->assertDatabaseHas('user_addresses', ['user_id' => $user->id]);
    }

    public function test_find_returns_address_when_exists(): void
    {
        $user = User::factory()->create();

        $address = UserAddress::create([
            'user_id'  => $user->id,
            'name'     => 'Work',
            'address'  => '456 Work Ave',
            'city'     => 'Melbourne',
            'postcode' => '3000',
            'country'  => 'AU',
        ]);

        $result = $this->repository->find($address->id);

        $this->assertInstanceOf(UserAddress::class, $result);
        $this->assertEquals($address->id, $result->id);
    }

    public function test_find_by_field_returns_addresses_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        UserAddress::create(['user_id' => $user1->id, 'name' => 'Home', 'address' => '1 First St', 'city' => 'Sydney', 'postcode' => '2000', 'country' => 'AU']);
        UserAddress::create(['user_id' => $user2->id, 'name' => 'Home', 'address' => '2 Second St', 'city' => 'Brisbane', 'postcode' => '4000', 'country' => 'AU']);

        $result = $this->repository->findByField('user_id', $user1->id);

        $this->assertCount(1, $result);
        $this->assertEquals($user1->id, $result->first()->user_id);
    }
}
