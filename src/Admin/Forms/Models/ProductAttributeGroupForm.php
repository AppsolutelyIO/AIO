<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\ProductAttribute;
use Appsolutely\AIO\Models\ProductAttributeGroup;

class ProductAttributeGroupForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        $this->relationships = ['attributes'];
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new ProductAttributeGroup();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('title', __t('Title'))->required();
        $this->text('remark', __t('Remark'));

        $this->multipleSelect('attributes', __t('Attributes'))
            ->options(ProductAttribute::status()->pluck('title', 'id'))
            ->customFormat(extract_values());

        $this->switch('status', __t('Status'))->default(true);
    }
}
