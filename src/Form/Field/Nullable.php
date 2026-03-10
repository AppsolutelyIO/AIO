<?php

namespace Appsolutely\AIO\Form\Field;

use Appsolutely\AIO\Form\Field;

class Nullable extends Field
{
    public function __construct()
    {
    }

    public function __call($method, $parameters)
    {
        return $this;
    }

    public function render()
    {
    }
}
