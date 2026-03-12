<?php

namespace Appsolutely\AIO\Tree\Actions;

use Appsolutely\AIO\Form;
use Appsolutely\AIO\Tree\RowAction;

class QuickEdit extends RowAction
{
    protected $dialogFormDimensions = ['700px', '670px'];

    public function html()
    {
        [$width, $height] = $this->dialogFormDimensions;

        Form::dialog(trans('admin.edit'))
            ->click('.tree-quick-edit')
            ->success('AIO.reload()')
            ->dimensions($width, $height);

        return <<<HTML
<a href="javascript:void(0);" data-url="{$this->resource()}/{$this->getKey()}/edit" class="tree-quick-edit"><i class="feather icon-edit"></i>&nbsp;</a>
HTML;
    }
}
