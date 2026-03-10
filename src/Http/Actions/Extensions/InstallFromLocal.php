<?php

namespace Appsolutely\AIO\Http\Actions\Extensions;

use Appsolutely\AIO\Grid\Tools\AbstractTool;
use Appsolutely\AIO\Http\Forms\InstallFromLocal as InstallFromLocalForm;
use Appsolutely\AIO\Widgets\Modal;

class InstallFromLocal extends AbstractTool
{
    protected $style = 'btn btn-primary';

    public function html()
    {
        return Modal::make()
            ->lg()
            ->title($title = trans('admin.install_from_local'))
            ->body(InstallFromLocalForm::make())
            ->button("<button class='btn btn-primary'><i class=\"feather icon-folder\"></i> &nbsp;{$title}</button> &nbsp;");
    }
}
