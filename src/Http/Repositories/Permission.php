<?php

namespace Appsolutely\AIO\Http\Repositories;

use Appsolutely\AIO\Repositories\EloquentRepository;

class Permission extends EloquentRepository
{
    public function __construct()
    {
        $this->eloquentClass = config('admin.database.permissions_model');

        parent::__construct();
    }
}
