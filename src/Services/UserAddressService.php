<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\UserAddress;
use Appsolutely\AIO\Repositories\UserAddressRepository;
use Appsolutely\AIO\Services\Contracts\UserAddressServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class UserAddressService implements UserAddressServiceInterface
{
    public function __construct(
        protected UserAddressRepository $userAddressRepository,
    ) {}

    public function getAddressesByUser(int $userId): Collection
    {
        return UserAddress::query()
            ->where('user_id', $userId)
            ->orderByDesc('sort')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findById(int $id): ?UserAddress
    {
        return UserAddress::query()->find($id);
    }

    public function createAddress(int $userId, array $data): UserAddress
    {
        return UserAddress::query()->create(array_merge(
            $data,
            ['user_id' => $userId],
        ));
    }

    public function updateAddress(UserAddress $address, array $data): UserAddress
    {
        $address->update($data);

        return $address->fresh();
    }

    public function deleteAddress(UserAddress $address): bool
    {
        return (bool) $address->delete();
    }
}
