<?php

namespace Appsolutely\AIO\Form\Field;

use Appsolutely\AIO\Support\Helper;

class MultipleSelect extends Select
{
    protected function formatFieldData($data)
    {
        return Helper::array($this->getValueFromData($data));
    }

    protected function prepareInputValue($value)
    {
        return Helper::array($value, true);
    }
}
