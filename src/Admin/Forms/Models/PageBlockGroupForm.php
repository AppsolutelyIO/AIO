<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\PageBlockGroup;

class PageBlockGroupForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new PageBlockGroup();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('title', __t('Title'))->required();
        $this->text('remark', __t('Remark'));
        $this->number('sort', __t('Sort'));
        $this->switch('status', __t('Status'));
    }
}
