<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Wishlist;

final class WishlistRepository extends BaseRepository
{
    public function model(): string
    {
        return Wishlist::class;
    }
}
