<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Observers;

use Appsolutely\AIO\Events\ProductCreated;
use Appsolutely\AIO\Events\ProductDeleted;
use Appsolutely\AIO\Events\ProductUpdated;
use Appsolutely\AIO\Models\Product;

/**
 * Observer for Product model events
 *
 * Dispatches domain events when products are created, updated, or deleted.
 * Listeners handle side effects like cache clearing.
 */
final class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        event(new ProductCreated($product));
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        event(new ProductUpdated($product));
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        event(new ProductDeleted($product));
    }
}
