<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\ProductAttribute;
use Appsolutely\AIO\Repositories\ProductAttributeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class ProductAttributeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductAttributeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductAttributeRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(ProductAttributeRepository::class, $this->repository);
    }

    public function test_model_returns_product_attribute_class(): void
    {
        $this->assertEquals(ProductAttribute::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_product_attribute(): void
    {
        $attribute = $this->repository->create([
            'title'  => 'Red',
            'slug'   => 'red-' . uniqid(),
            'status' => true,
        ]);

        $this->assertInstanceOf(ProductAttribute::class, $attribute);
        $this->assertEquals('Red', $attribute->title);
    }

    public function test_find_returns_attribute_when_exists(): void
    {
        $attribute = ProductAttribute::create([
            'title'  => 'Blue',
            'slug'   => 'blue-' . uniqid(),
            'status' => true,
        ]);

        $result = $this->repository->find($attribute->id);

        $this->assertInstanceOf(ProductAttribute::class, $result);
        $this->assertEquals($attribute->id, $result->id);
    }

    public function test_find_by_field_filters_by_status(): void
    {
        ProductAttribute::create(['title' => 'Active Attr', 'slug' => 'active-' . uniqid(), 'status' => true]);
        ProductAttribute::create(['title' => 'Inactive Attr', 'slug' => 'inactive-' . uniqid(), 'status' => false]);

        $result = $this->repository->findByField('status', true);

        $this->assertCount(1, $result);
    }
}
