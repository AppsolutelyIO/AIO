<?php

namespace Appsolutely\AIO\Form\Field;

use Closure;
use Appsolutely\AIO\Form\Field;

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
