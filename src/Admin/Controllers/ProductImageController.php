<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductImage;
use Appsolutely\AIO\Models\ProductSku;

final class ProductImageController extends AdminBaseController
{
    protected function grid(): Grid
    {
        return Grid::make(ProductImage::with(['product', 'productSku']), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('product.title', __t('Product'))->display(function ($title) {
                $productId = $this->product_id;
                $url       = admin_route('products.entry.edit', [$productId]);

                return "<a href='{$url}'>{$title}</a>";
            });
            $grid->column('productSku.title', __t('SKU'));
            $grid->column('path', __t('Image'))->image('', 80, 80);
            $grid->column('alt', __t('Alt Text'))->editable();
            $grid->column('sort', __t('Sort'))->editable()->sortable();
            $grid->column('is_primary', __t('Primary'))->switch();
            $grid->column('created_at', __t('Created At'))->display(column_time_format())->sortable();
            $grid->model()->orderBy('product_id')->orderBy('sort');

            $grid->quickSearch('id', 'alt');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(2);
                $filter->equal('product_id', __t('Product ID'))->width(3);
                $filter->equal('product_sku_id', __t('SKU ID'))->width(3);
                $filter->equal('is_primary', __t('Primary'))->select([0 => 'No', 1 => 'Yes'])->width(2);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(ProductImage::with(['product', 'productSku']), function (Form $form) {
            $form->display('id', __t('ID'));

            $form->select('product_id', __t('Product'))
                ->options(function ($id) {
                    if ($id) {
                        $product = Product::query()->find($id);

                        return $product ? [$product->id => $product->title] : [];
                    }

                    return [];
                })
                ->ajax(admin_route('products.entry.index'), 'id', 'title')
                ->required();

            $form->select('product_sku_id', __t('SKU'))
                ->options(function ($id) {
                    if ($id) {
                        $sku = ProductSku::query()->find($id);

                        return $sku ? [$sku->id => $sku->title] : [];
                    }

                    return [];
                })
                ->load('product_id', admin_route('products.skus.index'), 'id', 'title')
                ->help(__t('Optional: associate this image with a specific SKU'));

            $form->image('path', __t('Image'))
                ->autoUpload()
                ->url(upload_to_api(ProductImage::class, $form->getKey()))
                ->required();

            $form->text('alt', __t('Alt Text'))
                ->help(__t('Descriptive text for accessibility and SEO'));

            $form->number('sort', __t('Sort Order'))->default(0)
                ->help(__t('Lower numbers appear first'));

            $form->switch('is_primary', __t('Primary Image'))
                ->help(__t('The primary image is shown as the product thumbnail'));

            $form->disableViewButton();
            $form->disableViewCheck();
        });
    }
}
