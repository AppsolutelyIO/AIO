<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\ReleaseVersion;

class ReleaseVersionForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new ReleaseVersion();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('version', __t('Version'))->required();
        $this->text('remark', __t('Remark'));
        $this->text('release_channel', __t('Release Channel'));
        $this->switch('status', __t('Status'));
        $this->datetime('published_at', __t('Published At'));
    }
}
