<?php

namespace Appsolutely\AIO\Show;

use Appsolutely\AIO\Support\Helper;

class Html extends Field
{
    public $html;

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function render()
    {
        return Helper::render($this->html, [$this->value()], $this->parent->model());
    }
}
