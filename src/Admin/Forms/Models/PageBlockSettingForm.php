<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Repositories\PageRepository;

class PageBlockSettingForm extends ModelForm
{
    protected PageBlockSettingRepository $repository;

    protected PageRepository $pageRepository;

    protected PageBlockRepository $blockRepository;

    public function __construct(?int $id = null)
    {
        $this->relationships = ['blockValue'];
        parent::__construct($id);
        $this->repository      = app(PageBlockSettingRepository::class);
        $this->pageRepository  = app(PageRepository::class);
        $this->blockRepository = app(PageBlockRepository::class);
    }

    protected function initializeModel(): void
    {
        $this->model = new PageBlockSetting();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->select('page_id', __t('Page'))->options(
            $this->pageRepository->all()->pluck('title', 'id')->toArray()
        )->required();
        $this->select('block_id', __t('Block'))->options(
            $this->blockRepository->all()->pluck('title', 'id')->toArray()
        )->required();

        $this->text('type', __t('Type'));
        $this->text('remark', __t('Remark'));

        $this->text('blockValue.view_style', __t('View Style'))
            ->default('default')
            ->help(__t('View style variant (e.g. default, fullscreen)'));

        $this->text('blockValue.anchor_label', __t('Anchor Label'))
            ->help(__t('Section label for anchor navigation (leave empty to exclude)'));

        $this->textarea('blockValue.display_options', __t('Display Options'))
            ->rows(10)->help(__t('JSON format for display options'));

        $this->textarea('blockValue.query_options', __t('Query Options'))
            ->rows(10)->help(__t('JSON format for query options'));

        $this->text('blockValue.theme', __t('Theme'))
            ->help(__t('Theme name this value is for (empty = all themes)'));

        $this->number('sort', __t('Sort'));
        $this->switch('status', __t('Status'));
    }

    protected function updateModel(int $id, array $input): void
    {
        /** @var PageBlockSetting $model */
        $model = $this->repository->find($id);

        if (! $model) {
            throw new \RuntimeException('PageBlockSetting not found');
        }

        // Handle block value updates
        if (! empty($input['blockValue']) && $model->blockValue) {
            $blockValueChanged = false;

            if (isset($input['blockValue']['view_style'])) {
                $model->blockValue->view_style = $input['blockValue']['view_style'] !== ''
                    ? (string) $input['blockValue']['view_style']
                    : 'default';
                $blockValueChanged = true;
            }

            if (array_key_exists('anchor_label', $input['blockValue'])) {
                $model->blockValue->anchor_label = $input['blockValue']['anchor_label'] !== ''
                    ? (string) $input['blockValue']['anchor_label']
                    : null;
                $blockValueChanged = true;
            }

            if (isset($input['blockValue']['display_options'])) {
                $displayOptions = $input['blockValue']['display_options'];
                $parsed         = is_string($displayOptions) ? json_decode($displayOptions, true) : $displayOptions;

                if (is_array($parsed)) {
                    unset($parsed['anchor_label'], $parsed['style']);
                    $model->blockValue->display_options = $parsed;
                } else {
                    $model->blockValue->display_options = $displayOptions;
                }
                $blockValueChanged = true;
            }

            if (isset($input['blockValue']['query_options'])) {
                $model->blockValue->query_options = $input['blockValue']['query_options'];
                $blockValueChanged                = true;
            }

            if (isset($input['blockValue']['theme']) && $model->blockValue) {
                $model->blockValue->theme = $input['blockValue']['theme'] !== '' ? $input['blockValue']['theme'] : null;
                $blockValueChanged        = true;
            }

            if ($blockValueChanged) {
                $model->checkAndCreateNewBlockValue();
            }
            unset($input['blockValue']);
        }

        $this->repository->update($input, $id);
    }
}
