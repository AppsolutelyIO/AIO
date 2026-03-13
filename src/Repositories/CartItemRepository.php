<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\CartItem;

final class CartItemRepository extends BaseRepository
{
    public function model(): string
    {
        return CartItem::class;
    }
}
