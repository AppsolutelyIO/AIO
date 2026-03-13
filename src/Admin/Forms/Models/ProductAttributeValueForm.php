<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\ProductAttribute;
use Appsolutely\AIO\Models\ProductAttributeValue;

class ProductAttributeValueForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new ProductAttributeValue();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->select('product_attribute_id', 'Attribute')
            ->options(ProductAttribute::status()->pluck('title', 'id'))
            ->required();
        $this->text('value', __t('Value'))->required();
        $this->text('slug', __t('Slug'));
        $this->switch('status', __t('Status'))->default(true);
    }
}
