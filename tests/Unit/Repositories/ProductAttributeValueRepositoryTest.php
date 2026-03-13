<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\ProductAttribute;
use Appsolutely\AIO\Models\ProductAttributeValue;
use Appsolutely\AIO\Repositories\ProductAttributeValueRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class ProductAttributeValueRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductAttributeValueRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(ProductAttributeValueRepository::class);
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(ProductAttributeValueRepository::class, $this->repository);
    }

    public function test_model_returns_product_attribute_value_class(): void
    {
        $this->assertEquals(ProductAttributeValue::class, $this->repository->model());
    }

    public function test_all_returns_collection(): void
    {
        $result = $this->repository->all();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_create_stores_product_attribute_value(): void
    {
        $attribute = ProductAttribute::create([
            'title'  => 'Color',
            'slug'   => 'color-' . uniqid(),
            'status' => true,
        ]);

        $value = $this->repository->create([
            'product_attribute_id' => $attribute->id,
            'value'                => 'Red',
            'slug'                 => 'red-' . uniqid(),
            'status'               => true,
        ]);

        $this->assertInstanceOf(ProductAttributeValue::class, $value);
        $this->assertEquals('Red', $value->value);
    }

    public function test_find_returns_value_when_exists(): void
    {
        $attribute = ProductAttribute::create([
            'title'  => 'Size',
            'slug'   => 'size-' . uniqid(),
            'status' => true,
        ]);

        $attrValue = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value'                => 'Large',
            'slug'                 => 'large-' . uniqid(),
            'status'               => true,
        ]);

        $result = $this->repository->find($attrValue->id);

        $this->assertInstanceOf(ProductAttributeValue::class, $result);
        $this->assertEquals($attrValue->id, $result->id);
    }

    public function test_find_by_field_returns_values_for_attribute(): void
    {
        $attribute1 = ProductAttribute::create(['title' => 'Attr1', 'slug' => 'attr1-' . uniqid(), 'status' => true]);
        $attribute2 = ProductAttribute::create(['title' => 'Attr2', 'slug' => 'attr2-' . uniqid(), 'status' => true]);

        ProductAttributeValue::create(['product_attribute_id' => $attribute1->id, 'value' => 'V1', 'slug' => 'v1-' . uniqid(), 'status' => true]);
        ProductAttributeValue::create(['product_attribute_id' => $attribute2->id, 'value' => 'V2', 'slug' => 'v2-' . uniqid(), 'status' => true]);

        $result = $this->repository->findByField('product_attribute_id', $attribute1->id);

        $this->assertCount(1, $result);
        $this->assertEquals($attribute1->id, $result->first()->product_attribute_id);
    }
}
