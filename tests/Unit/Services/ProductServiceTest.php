<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductService::class);
    }

    // --- getProductTypes ---

    public function test_get_product_types_returns_array(): void
    {
        $result = $this->service->getProductTypes();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_get_product_types_contains_all_enum_cases(): void
    {
        $result = $this->service->getProductTypes();

        // ProductType enum has Physical, AutoVirtual, ManualVirtual
        $this->assertGreaterThanOrEqual(3, count($result));
    }

    public function test_get_product_types_values_are_strings(): void
    {
        $result = $this->service->getProductTypes();

        foreach ($result as $key => $label) {
            $this->assertIsString($key);
            $this->assertIsString($label);
        }
    }

    // --- getShipmentMethodForManualVirtualProduct ---

    public function test_get_shipment_method_for_manual_virtual_returns_array(): void
    {
        $result = $this->service->getShipmentMethodForManualVirtualProduct();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_get_shipment_method_for_manual_virtual_matches_constant(): void
    {
        $result = $this->service->getShipmentMethodForManualVirtualProduct();

        $this->assertEquals(Product::SHIPMENT_METHOD_MANUAL_DELIVERABLE_VIRTUAL_PRODUCT, $result);
    }

    // --- getShipmentMethodForAutoVirtualProduct ---

    public function test_get_shipment_method_for_auto_virtual_returns_array(): void
    {
        $result = $this->service->getShipmentMethodForAutoVirtualProduct();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_get_shipment_method_for_auto_virtual_matches_constant(): void
    {
        $result = $this->service->getShipmentMethodForAutoVirtualProduct();

        $this->assertEquals(Product::SHIPMENT_METHOD_AUTO_DELIVERABLE_VIRTUAL_PRODUCT, $result);
    }

    // --- getShipmentMethodForPhysicalProduct ---

    public function test_get_shipment_method_for_physical_returns_array(): void
    {
        $result = $this->service->getShipmentMethodForPhysicalProduct();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_get_shipment_method_for_physical_matches_constant(): void
    {
        $result = $this->service->getShipmentMethodForPhysicalProduct();

        $this->assertEquals(Product::SHIPMENT_METHOD_PHYSICAL_PRODUCT, $result);
    }

    // --- shipment methods are distinct ---

    public function test_shipment_methods_are_all_different(): void
    {
        $manual   = $this->service->getShipmentMethodForManualVirtualProduct();
        $auto     = $this->service->getShipmentMethodForAutoVirtualProduct();
        $physical = $this->service->getShipmentMethodForPhysicalProduct();

        $this->assertNotEquals($manual, $auto);
        $this->assertNotEquals($manual, $physical);
        $this->assertNotEquals($auto, $physical);
    }
}
