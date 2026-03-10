<?php

namespace Appsolutely\AIO\Form\Field;

class Rate extends Text
{
    public function render()
    {
        $this->prepend('%')->defaultAttribute('placeholder', 0);

        return parent::render();
    }
}
