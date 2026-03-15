<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Admin\Actions\Grid\DeleteAction;
use Appsolutely\AIO\Admin\Forms\Fields\Markdown;
use Appsolutely\AIO\Admin\Forms\ProductSkuForm;
use Appsolutely\AIO\Admin\Forms\ProductSkuGeneratorForm;
use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Repositories\ProductCategoryRepository;
use Appsolutely\AIO\Services\Contracts\ProductServiceInterface;
use Appsolutely\AIO\Widgets\Modal;

final class ProductController extends AdminBaseController
{
    public function __construct(
        protected ProductCategoryRepository $productCategoryRepository,
        protected ProductServiceInterface $productService
    ) {}

    protected function grid(): Grid
    {
        return Grid::make(Product::with(['categories', 'skus']), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('title', __t('Title'));
            $grid->column('categories', __t('Categories'))->pluck('title')->label();
            $grid->column('skus', __t('SKUs'))->display(column_count());

            $productTypes = $this->productService->getProductTypes();
            $grid->column('type', __t('Type'))->display(function ($type) use ($productTypes) {
                $key = $type instanceof \BackedEnum ? $type->value : $type;

                return $productTypes[$key] ?? $key;
            })->label();

            $grid->column('published_at', __t('Published At'))->display(column_time_format())->sortable();
            $grid->column('expired_at', __t('Expired At'))->display(column_time_format())->sortable();
            $grid->column('sort', __t('Sort'))->editable();
            $grid->column('status', __t('Status'))->switch();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(3);
                $filter->like('title')->width(3);
                $filter->equal('type')->select($this->productService->getProductTypes())->width(3);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(Product::with(['categories']), function (Form $form) {
            $form->defaultEditingChecked();

            $form->tab('Basic', function (Form $form) {
                $this->basicForm($form);
            }, true, 'basic')->tab('SKUs', function (Form $form) {
                $form->html($this->skusGrid($form->getKey()));
            }, false, 'skus')->tab('Optional', function (Form $form) {
                $this->optionalForm($form);
            }, false, 'optional');
        });
    }

    protected function basicForm(Form $form): Form
    {
        $form->display('id', __t('ID'));
        $form->radio('type', __t('Type'))->options($this->productService->getProductTypes())
            ->default(ProductType::Physical->value)
            ->when(ProductType::Physical->value, function (Form $form) {
                $shipmentMethods = associative_array($this->productService->getShipmentMethodForPhysicalProduct());
                $form->multipleSelect('shipment_methods', __t('Shipment Methods'))
                    ->options($shipmentMethods)
                    ->default(array_shift($shipmentMethods));
            })
            ->when(ProductType::AutoVirtual->value, function (Form $form) {
                $shipmentMethods = associative_array($this->productService->getShipmentMethodForAutoVirtualProduct());
                $form->multipleSelect('shipment_methods', __t('Shipment Methods'))
                    ->options($shipmentMethods)
                    ->default(array_shift($shipmentMethods));
            })
            ->when(ProductType::ManualVirtual->value, function (Form $form) {
                $shipmentMethods = associative_array($this->productService->getShipmentMethodForManualVirtualProduct());
                $form->multipleSelect('shipment_methods', __t('Shipment Methods'))
                    ->options($shipmentMethods)
                    ->default(array_shift($shipmentMethods));
            })->required();

        $availableCategories = $this->productCategoryRepository->getActiveList();
        $form->multipleSelect('categories', __t('Categories'))
            ->options($availableCategories)
            ->customFormat(extract_values());

        $form->text('title', __t('Title'))->required();
        $form->text('subtitle', __t('Subtitle'));
        $form->text('slug', __t('Slug'));
        $form->markdown('content', __t('Content'))->options(Markdown::options())->script(Markdown::script());

        $form->currency('original_price', __t('Original Price'))->symbol(app_currency_symbol())->default(99900)
            ->customFormat(subunit_to_display())
            ->saving(display_to_subunit());
        $form->currency('price', __t('Price'))->symbol(app_currency_symbol())->default(99900)
            ->customFormat(subunit_to_display())
            ->saving(display_to_subunit());

        $form->datetime('published_at', __t('Published At'));
        $form->datetime('expired_at', __t('Expired At'));
        $form->switch('status', __t('Status'));

        $form->disableViewButton();
        $form->disableViewCheck();

        return $form;
    }

    protected function optionalForm(Form $form): Form
    {
        $form->image('cover', __t('Cover'))->autoUpload()->url(upload_to_api(Product::class, $form->getKey()));
        $form->textarea('keywords', __t('Keywords'))->rows(2);
        $form->textarea('description', __t('Description'))->rows(3);

        $form->keyValue('setting', __t('Setting'))->default([])
            ->setKeyLabel('Key')
            ->setValueLabel('Value')
            ->saveAsJson();

        $form->multipleSelect('payment_methods', __t('Payment Methods'));
        // ->options($this->paymentRepository->list()->pluck('title', 'id'));

        $form->table('additional_columns', function (Form\NestedForm $table) {
            $table->text('field');
            $table->text('name');
            $table->select('input_type')->options([
                'input' => 'Input',
                'text'  => 'Text',
            ]);
            $table->switch('required')->default(true);
        })->saving(function ($value) {
            return json_encode($value);
        });

        $form->disableViewButton();
        $form->disableViewCheck();

        return $form;
    }

    protected function skusGrid($productId): Grid
    {
        return Grid::make(ProductSku::with(['product']), function (Grid $grid) use ($productId) {
            $grid->model()->where('product_id', $productId);

            $grid->column('id')->sortable();
            $grid->column('attributes')->display(column_value_simple('value', 'data'));
            $grid->column('title')->editable();
            $grid->column('slug')->editable();
            $grid->column('original_price')->display(fn ($v) => format_cents($v))->editable();
            $grid->column('price')->display(fn ($v) => format_cents($v))->editable();
            $grid->column('stock')->editable();
            $grid->column('status')->switch();
            $grid->column('sort')->sortable()->editable();

            // Add create button that opens the modal form
            $grid->tools(function (Grid\Tools $tools) use ($productId) {
                $modal = Modal::make()
                    ->lg()
                    ->title('Create SKUs')
                    ->body(ProductSkuGeneratorForm::make()->payload(['product_id' => $productId]))
                    ->button(admin_create_button());

                $tools->append($modal);
            });

            $grid->disableCreateButton();

            // Show row actions with edit in modal
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableDelete();

                // Add custom edit button that opens in modal
                $editModal = Modal::make()->xl()->scrollable()
                    ->title('Edit SKU')
                    ->body(ProductSkuForm::make()->payload([
                        'product_id' => $actions->row->product_id,
                        'id'         => $actions->row->id,
                    ]))
                    ->button(admin_edit_action());
                $actions->append($editModal);
                $actions->append(new DeleteAction());
            });

            $grid->setResource(admin_route('products.skus.index'));
        });
    }
}
