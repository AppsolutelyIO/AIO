<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\WishlistItem;

final class WishlistItemRepository extends BaseRepository
{
    public function model(): string
    {
        return WishlistItem::class;
    }
}
