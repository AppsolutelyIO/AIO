<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Cart;

final class CartRepository extends BaseRepository
{
    public function model(): string
    {
        return Cart::class;
    }
}
