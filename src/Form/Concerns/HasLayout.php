<?php

namespace Appsolutely\AIO\Form\Concerns;

use Appsolutely\AIO\Form\Layout;
use Closure;

trait HasLayout
{
    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @param  int|float  $width
     * @return $this
     */
    public function column($width, Closure $callback)
    {
        $this->layout()->onlyColumn($width, function () use ($callback) {
            $callback($this);
        });

        return $this;
    }

    /**
     * @return Layout
     */
    public function layout()
    {
        return $this->layout ?: ($this->layout = new Layout($this));
    }
}
