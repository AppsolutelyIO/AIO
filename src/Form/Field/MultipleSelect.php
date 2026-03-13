<?php

namespace Appsolutely\AIO\Form\Field;

use Appsolutely\AIO\Support\ArrayHelper;

class MultipleSelect extends Select
{
    protected function formatFieldData($data)
    {
        return ArrayHelper::convert($this->getValueFromData($data));
    }

    protected function prepareInputValue($value)
    {
        return ArrayHelper::convert($value, true);
    }
}
