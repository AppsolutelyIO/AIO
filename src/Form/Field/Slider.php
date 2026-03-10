<?php

namespace Appsolutely\AIO\Form\Field;

use Appsolutely\AIO\Form\Field;

class Slider extends Field
{
    protected $options = [
        'type'     => 'single',
        'prettify' => false,
        'hasGrid'  => true,
    ];
}
