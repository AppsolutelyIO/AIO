<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\UserAddress;

final class UserAddressRepository extends BaseRepository
{
    public function model(): string
    {
        return UserAddress::class;
    }
}
