<?php

namespace Appsolutely\AIO\Http\Repositories;

use Appsolutely\AIO\Repositories\EloquentRepository;

class Role extends EloquentRepository
{
    public function __construct($relations = [])
    {
        $this->eloquentClass = config('admin.database.roles_model');

        parent::__construct($relations);
    }
}
