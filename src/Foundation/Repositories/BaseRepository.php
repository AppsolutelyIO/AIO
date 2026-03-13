<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Foundation\Repositories;

use Prettus\Repository\Eloquent\BaseRepository as Repository;

class BaseRepository extends Repository
{
    /**
     * Find by field and return the first result, or null.
     */
    public function findByFieldFirst(string $field, mixed $value, array $columns = ['*']): ?object
    {
        $found = $this->findByField($field, $value, $columns);

        return $found->first();
    }

    /**
     * Specify Model class name.
     *
     * Child repositories must implement this method to return the model class name.
     */
    public function model(): string
    {
        throw new \RuntimeException(
            'BaseRepository::model() must be implemented by child repository classes. '
            . 'Return the fully qualified model class name (e.g., return Page::class;).'
        );
    }
}
