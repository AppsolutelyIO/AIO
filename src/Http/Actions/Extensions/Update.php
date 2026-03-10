<?php

namespace Appsolutely\AIO\Http\Actions\Extensions;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Grid\RowAction;

class Update extends RowAction
{
    public function title()
    {
        $replace = ['version' => $this->row->extension->getLocalLatestVersion()];

        return sprintf('<b>%s</b>', trans('admin.upgrade_to_version', $replace));
    }

    public function handle()
    {
        $manager = Admin::extension()
            ->updateManager()
            ->update($this->getKey());

        return $this
            ->response()
            ->success(implode('<br>', $manager->notes))
            ->refresh();
    }
}
