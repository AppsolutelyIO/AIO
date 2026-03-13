<?php

namespace Appsolutely\AIO\Show;

use Appsolutely\AIO\Support\HtmlHelper;

class Html extends Field
{
    public $html;

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function render()
    {
        return HtmlHelper::render($this->html, [$this->value()], $this->parent->model());
    }
}
