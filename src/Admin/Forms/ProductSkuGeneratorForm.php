<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms;

use Appsolutely\AIO\Contracts\LazyRenderable;
use Appsolutely\AIO\Models\ProductAttributeGroup;
use Appsolutely\AIO\Repositories\ProductSkuRepository;
use Appsolutely\AIO\Traits\LazyWidget;
use Appsolutely\AIO\Widgets\Form;

class ProductSkuGeneratorForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function handle(array $input)
    {
        try {
            // Get selected combinations
            $combinations = $input['attribute_combinations'] ?? [];
            if (empty($combinations)) {
                return $this->response()->error('Please select at least one attribute combination');
            }

            // Begin transaction
            \DB::beginTransaction();

            $duplicates = [];

            foreach ($combinations as $combinationKey) {
                // Get cached combination data
                $combinationData = cache()->get($combinationKey);
                if (! $combinationData) {
                    continue;
                }

                // Check if SKU with the same product_id and attributes['key'] exists
                $productSkuRepository = app(ProductSkuRepository::class);
                $existingSku          = $productSkuRepository->getSkusBySkuKey($combinationKey, $input['product_id']);

                if ($existingSku) {
                    $duplicates[] = $combinationData['readable'] ?? 'Unknown combination';

                    continue;
                }

                // Create SKU
                $data = [
                    'product_id'     => $input['product_id'],
                    'title'          => $input['title_prefix'] ? $input['title_prefix'] . ' - ' . $combinationData['readable'] : $combinationData['readable'],
                    'attributes'     => $combinationData,
                    'original_price' => $input['original_price'],
                    'price'          => $input['price'],
                    'stock'          => $input['stock'],
                    'status'         => $input['status'] ?? true,
                ];
                $productSkuRepository->create($data)->attributeValues()->sync(array_column($combinationData['data'], 'id'));
            }

            \DB::commit();

            if (! empty($duplicates)) {
                return $this->response()
                    ->warning('Some SKUs were skipped because they already exist: ' . implode(', ', $duplicates))
                    ->refresh();
            }

            return $this->response()->success('SKUs generated successfully')->refresh();

        } catch (\Exception $e) {
            \DB::rollBack();

            return $this->response()->error('Failed to generate SKUs: ' . $e->getMessage());
        }
    }

    public function form()
    {
        $productId = $this->payload['product_id'] ?? null;

        $this->hidden('product_id')->value($productId);

        // Add attribute group selector
        $this->select('attribute_group_id', 'Attribute Group')
            ->options(ProductAttributeGroup::status()->pluck('title', 'id'))
            ->load('attribute_combinations', admin_route('api.attribute-groups'), 'key', 'readable');

        // Add multiselect for attribute combinations
        $this->multipleSelect('attribute_combinations', 'Attribute Combinations')
            ->help('Select attribute combinations to generate SKUs');

        // Basic SKU fields
        $this->text('title_prefix', 'Title Prefix')
            ->help('This will be prefixed to each generated SKU title');

        $this->currency('original_price', 'Original Price')
            ->symbol(app_currency_symbol())
            ->default(999);

        $this->currency('price', 'Price')
            ->symbol(app_currency_symbol())
            ->default(999);

        $this->number('stock', 'Stock')
            ->default(999)
            ->min(1);

        $this->switch('status', 'Status')
            ->default(false);
    }

    /**
     * Default values for the form
     */
    public function default()
    {
        return [
            'status'         => false,
            'stock'          => 999,
            'original_price' => 999,
            'price'          => 999,
        ];
    }
}
