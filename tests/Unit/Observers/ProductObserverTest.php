<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Observers;

use Appsolutely\AIO\Events\ProductCreated;
use Appsolutely\AIO\Events\ProductDeleted;
use Appsolutely\AIO\Events\ProductUpdated;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Observers\ProductObserver;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Support\Facades\Event;

final class ProductObserverTest extends TestCase
{
    private ProductObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->observer = new ProductObserver();
    }

    // --- created ---

    public function test_created_dispatches_product_created_event(): void
    {
        Event::fake([ProductCreated::class]);

        $product = new Product(['title' => 'Test Product']);
        $this->observer->created($product);

        Event::assertDispatched(ProductCreated::class, function (ProductCreated $event) use ($product) {
            return $event->product === $product;
        });
    }

    // --- updated ---

    public function test_updated_dispatches_product_updated_event(): void
    {
        Event::fake([ProductUpdated::class]);

        $product = new Product(['title' => 'Updated Product']);
        $this->observer->updated($product);

        Event::assertDispatched(ProductUpdated::class, function (ProductUpdated $event) use ($product) {
            return $event->product === $product;
        });
    }

    // --- deleted ---

    public function test_deleted_dispatches_product_deleted_event(): void
    {
        Event::fake([ProductDeleted::class]);

        $product = new Product(['title' => 'Deleted Product']);
        $this->observer->deleted($product);

        Event::assertDispatched(ProductDeleted::class, function (ProductDeleted $event) use ($product) {
            return $event->product === $product;
        });
    }

    // --- Event dispatch count ---

    public function test_each_lifecycle_event_dispatches_exactly_once(): void
    {
        Event::fake([ProductCreated::class, ProductUpdated::class, ProductDeleted::class]);

        $product = new Product(['title' => 'Test']);

        $this->observer->created($product);
        $this->observer->updated($product);
        $this->observer->deleted($product);

        Event::assertDispatchedTimes(ProductCreated::class, 1);
        Event::assertDispatchedTimes(ProductUpdated::class, 1);
        Event::assertDispatchedTimes(ProductDeleted::class, 1);
    }
}
