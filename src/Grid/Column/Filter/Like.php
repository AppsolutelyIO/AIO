<?php

namespace Appsolutely\AIO\Grid\Column\Filter;

use Appsolutely\AIO\Grid\Model;

class Like extends Equal
{
    /**
     * Add a binding to the query.
     *
     * @param  string  $value
     * @param  Model|null  $model
     */
    public function addBinding($value, Model $model)
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        $this->withQuery($model, 'where', ['like', "%{$value}%"]);
    }
}
