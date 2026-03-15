<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Admin\Forms\ProductSkuForm;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Repositories\ProductRepository;
use Appsolutely\AIO\Widgets\Modal;

final class ProductSkuController extends AdminBaseController
{
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(ProductSku::with(['product']), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('product.title', __t('Product'));
            $grid->column('attributes', __t('Attributes'))->display(column_value_simple('value', 'data'));
            $grid->column('title', __t('Title'))->editable();
            $grid->column('slug', __t('Slug'))->editable();
            $grid->column('stock', __t('Stock'))->editable();
            $grid->column('original_price', __t('Original Price'))->editable();
            $grid->column('price', __t('Price'))->editable();
            $grid->column('sort', __t('Sort'))->editable()->sortable();
            $grid->column('status', __t('Status'))->switch();
            $grid->column('published_at', __t('Published At'))->display(column_time_format())->sortable();
            $grid->column('expired_at', __t('Expired At'))->display(column_time_format())->sortable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $products = $this->productRepository->getActiveList();
                $filter->equal('product_id', __t('Product'))->select($products)->width(3);
                $filter->like('title', __t('Title'))->width(3);
                $filter->equal('status', __t('Status'))->select(Status::toArray())->width(3);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableEdit();

                // Add the custom edit button that opens in modal
                $editModal = Modal::make()->xl()->scrollable()
                    ->title('Edit SKU')
                    ->body(ProductSkuForm::make()->payload([
                        'product_id' => $actions->row->product_id,
                        'id'         => $actions->row->id,
                    ]))
                    ->button(admin_edit_action());
                $actions->prepend($editModal);
            });
        });
    }
}
