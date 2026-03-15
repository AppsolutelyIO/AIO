<?php

namespace Appsolutely\AIO\Form\Field;

use Appsolutely\AIO\Form\Field;
use Closure;

class Display extends Field
{
    protected $callback;

    public function with(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function render()
    {
        if ($this->callback instanceof Closure) {
            $this->value = $this->callback->call($this->values(), $this->value());
        }

        return parent::render();
    }
}
