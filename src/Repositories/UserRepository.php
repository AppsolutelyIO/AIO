<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;

final class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return Model::userModel();
    }

    /**
     * Search users by name or email.
     */
    public function search(string $term): Builder
    {
        return $this->model->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?Authenticatable
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get active users (with verified email)
     */
    public function getActiveUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->whereNotNull('email_verified_at')->get();
    }

    /**
     * Get users with pagination
     */
    public function getPaginated(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
