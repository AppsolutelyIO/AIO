<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use App\Models\User;
use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Repositories\OrderRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private OrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(OrderRepository::class);
    }

    // --- Container resolution ---

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(OrderRepository::class, $this->repository);
    }

    public function test_model_returns_order_class(): void
    {
        $this->assertEquals(Order::class, $this->repository->model());
    }

    // --- all ---

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_all_returns_all_orders(): void
    {
        Order::factory()->count(3)->create();

        $result = $this->repository->all();

        $this->assertCount(3, $result);
    }

    public function test_all_returns_empty_when_no_orders(): void
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    // --- find ---

    public function test_find_returns_order_when_exists(): void
    {
        $order = Order::factory()->create();

        $result = $this->repository->find($order->id);

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals($order->id, $result->id);
    }

    // --- create ---

    public function test_create_stores_order(): void
    {
        $user = User::factory()->create();

        $order = $this->repository->create([
            'user_id'           => $user->id,
            'reference'         => 'ORDER-001',
            'summary'           => 'Test order',
            'amount'            => 10000,
            'discounted_amount' => 0,
            'total_amount'      => 10000,
            'status'            => OrderStatus::Pending->value,
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', ['reference' => 'ORDER-001']);
    }

    public function test_create_stores_order_with_reference(): void
    {
        $user = User::factory()->create();

        $order = $this->repository->create([
            'user_id'           => $user->id,
            'reference'         => 'ORDER-REF-TEST',
            'summary'           => 'Reference test',
            'amount'            => 10000,
            'discounted_amount' => 0,
            'total_amount'      => 10000,
            'status'            => OrderStatus::Pending->value,
        ]);

        $this->assertSame('ORDER-REF-TEST', $order->reference);
        $this->assertSame('Reference test', $order->summary);
    }

    // --- update ---

    public function test_update_modifies_order(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

        $this->repository->update(['status' => OrderStatus::Paid->value], $order->id);

        $this->assertDatabaseHas('orders', [
            'id'     => $order->id,
            'status' => OrderStatus::Paid->value,
        ]);
    }

    // --- delete ---

    public function test_delete_soft_deletes_order(): void
    {
        $order = Order::factory()->create();

        $this->repository->delete($order->id);

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    // --- findByField ---

    public function test_find_by_field_returns_matching_orders(): void
    {
        Order::factory()->count(2)->create(['status' => OrderStatus::Pending->value]);
        Order::factory()->create(['status' => OrderStatus::Completed->value]);

        $result = $this->repository->findByField('status', OrderStatus::Pending->value);

        $this->assertCount(2, $result);
    }

    public function test_find_by_field_returns_empty_when_no_match(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending->value]);

        $result = $this->repository->findByField('status', OrderStatus::Shipped->value);

        $this->assertEmpty($result);
    }

    // --- findByFieldFirst ---

    public function test_find_by_field_first_returns_single_order(): void
    {
        $order = Order::factory()->create(['reference' => 'UNIQUE-REF']);

        $result = $this->repository->findByFieldFirst('reference', 'UNIQUE-REF');

        $this->assertInstanceOf(Order::class, $result);
        $this->assertEquals($order->id, $result->id);
    }

    public function test_find_by_field_first_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByFieldFirst('reference', 'DOES-NOT-EXIST');

        $this->assertNull($result);
    }
}
