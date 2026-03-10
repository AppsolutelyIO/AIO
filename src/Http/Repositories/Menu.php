<?php

namespace Appsolutely\AIO\Http\Repositories;

use Appsolutely\AIO\Repositories\EloquentRepository;

class Menu extends EloquentRepository
{
    public function __construct($modelOrRelations = [])
    {
        $this->eloquentClass = config('admin.database.menu_model');

        parent::__construct($modelOrRelations);
    }
}
