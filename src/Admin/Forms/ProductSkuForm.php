<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms;

use Appsolutely\AIO\Admin\Forms\Fields\Markdown;
use Appsolutely\AIO\Models\ProductSku;
use Appsolutely\AIO\Contracts\LazyRenderable;
use Appsolutely\AIO\Traits\LazyWidget;
use Appsolutely\AIO\Widgets\Form;

class ProductSkuForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        try {
            $id = $this->payload['id'] ?? null;

            if ($id) {
                $sku = ProductSku::findOrFail($id);
                $sku->update($input);

                return $this->response()->success(__t('SKU updated successfully'))->refresh();
            } else {
                ProductSku::create($input);

                return $this->response()->success(__t('SKU created successfully'))->refresh();
            }
        } catch (\Exception $e) {
            return $this->response()->error(__t('Failed to save SKU: ') . $e->getMessage());
        }
    }

    public function form()
    {
        // Get data from payload
        $id        = $this->payload['id'] ?? null;
        $productId = $this->payload['product_id'] ?? null;

        $this->tab('Basic', function (Form $form) use ($id, $productId) {
            if ($id) {
                $sku = ProductSku::with(['product'])->findOrFail($id);
                $form->fill($sku);

                $form->display('id');
                $form->display('product.title', 'Product');
            } else {
                $form->hidden('product_id')->value($productId);
            }

            $form->text('title')->required();
            $form->text('subtitle');
            $form->text('slug')->help(__t('Leave empty to auto-generate from title'));
            $form->image('cover')->autoUpload()->url(upload_to_api(ProductSku::class, $id));

            $form->currency('original_price')->symbol(app_currency_symbol())->default(99900)
                ->customFormat(subunit_to_display())
                ->saving(display_to_subunit())
                ->required();
            $form->currency('price')->symbol(app_currency_symbol())->default(99900)
                ->customFormat(subunit_to_display())
                ->saving(display_to_subunit())
                ->required();
            $form->number('stock')->default(999)->min(0)->required();

            $form->number('sort')->default(99);
            $form->switch('status');

        }, true, 'sku_basic');

        $this->tab('SEO', function (Form $form) {
            $form->textarea('keywords')->rows(2);
            $form->textarea('description')->rows(3);
            $form->markdown('content')->options(Markdown::options())->script(Markdown::script());
        }, false, 'sku_seo');

        $this->tab('Optional', function (Form $form) {
            $form->display('_created_at');
            $form->display('_updated_at');
        }, false, 'sku_optional');
    }

    public function default()
    {
        return [
            'title' => __t('Untitled'),
        ];
    }
}
