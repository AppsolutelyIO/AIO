<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use App\Models\User;
use Appsolutely\AIO\Models\UserAddress;
use Appsolutely\AIO\Services\UserAddressService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAddressServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserAddressService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserAddressService::class);
    }

    public function test_create_address(): void
    {
        $user = User::factory()->create();

        $address = $this->service->createAddress($user->id, [
            'name'     => 'John Doe',
            'mobile'   => '1234567890',
            'address'  => '123 Main St',
            'city'     => 'New York',
            'province' => 'NY',
            'postcode' => '10001',
            'country'  => 'US',
        ]);

        $this->assertInstanceOf(UserAddress::class, $address);
        $this->assertEquals($user->id, $address->user_id);
        $this->assertEquals('John Doe', $address->name);
        $this->assertEquals('New York', $address->city);
    }

    public function test_get_addresses_by_user(): void
    {
        $user = User::factory()->create();
        UserAddress::query()->create(['user_id' => $user->id, 'name' => 'Home', 'address' => '123 Main', 'city' => 'NYC', 'country' => 'US']);
        UserAddress::query()->create(['user_id' => $user->id, 'name' => 'Work', 'address' => '456 Broadway', 'city' => 'NYC', 'country' => 'US']);

        $addresses = $this->service->getAddressesByUser($user->id);

        $this->assertCount(2, $addresses);
    }

    public function test_find_by_id(): void
    {
        $user    = User::factory()->create();
        $address = UserAddress::query()->create(['user_id' => $user->id, 'name' => 'Home', 'address' => '123 Main', 'city' => 'NYC', 'country' => 'US']);

        $found = $this->service->findById($address->id);

        $this->assertNotNull($found);
        $this->assertEquals($address->id, $found->id);
    }

    public function test_update_address(): void
    {
        $user    = User::factory()->create();
        $address = UserAddress::query()->create(['user_id' => $user->id, 'name' => 'Home', 'address' => '123 Main', 'city' => 'NYC', 'country' => 'US']);

        $updated = $this->service->updateAddress($address, ['name' => 'Office']);

        $this->assertEquals('Office', $updated->name);
    }

    public function test_delete_address(): void
    {
        $user    = User::factory()->create();
        $address = UserAddress::query()->create(['user_id' => $user->id, 'name' => 'Home', 'address' => '123 Main', 'city' => 'NYC', 'country' => 'US']);

        $result = $this->service->deleteAddress($address);

        $this->assertTrue($result);
        $this->assertSoftDeleted($address);
    }
}
