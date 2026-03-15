<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\ProductAttributeGroup;
use Appsolutely\AIO\Repositories\ProductAttributeGroupRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class ProductAttributeGroupRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductAttributeGroupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductAttributeGroupRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(ProductAttributeGroupRepository::class, $this->repository);
    }

    public function test_model_returns_product_attribute_group_class(): void
    {
        $this->assertEquals(ProductAttributeGroup::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_create_stores_product_attribute_group(): void
    {
        $group = $this->repository->create([
            'title'  => 'Size',
            'remark' => 'Size variants',
            'status' => true,
        ]);

        $this->assertInstanceOf(ProductAttributeGroup::class, $group);
        $this->assertDatabaseHas('product_attribute_groups', ['title' => 'Size']);
    }

    public function test_find_returns_group_when_exists(): void
    {
        $group = ProductAttributeGroup::create([
            'title'  => 'Color',
            'status' => true,
        ]);

        $result = $this->repository->find($group->id);

        $this->assertInstanceOf(ProductAttributeGroup::class, $result);
        $this->assertEquals($group->id, $result->id);
    }

    public function test_find_by_field_returns_active_groups(): void
    {
        ProductAttributeGroup::create(['title' => 'Active Group', 'status' => true]);
        ProductAttributeGroup::create(['title' => 'Inactive Group', 'status' => false]);

        $result = $this->repository->findByField('status', true);

        $this->assertCount(1, $result);
        $this->assertEquals('Active Group', $result->first()->title);
    }
}
