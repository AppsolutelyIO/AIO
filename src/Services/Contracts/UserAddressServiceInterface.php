<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\UserAddress;
use Illuminate\Database\Eloquent\Collection;

interface UserAddressServiceInterface
{
    /**
     * Get all addresses for a user.
     *
     * @return Collection<int, UserAddress>
     */
    public function getAddressesByUser(int $userId): Collection;

    /**
     * Find a specific address by ID.
     */
    public function findById(int $id): ?UserAddress;

    /**
     * Create a new address for a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAddress(int $userId, array $data): UserAddress;

    /**
     * Update an existing address.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateAddress(UserAddress $address, array $data): UserAddress;

    /**
     * Delete an address (soft delete).
     */
    public function deleteAddress(UserAddress $address): bool;
}
