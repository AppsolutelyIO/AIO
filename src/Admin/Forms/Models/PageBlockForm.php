<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Repositories\PageBlockGroupRepository;
use Appsolutely\AIO\Repositories\PageBlockRepository;

class PageBlockForm extends ModelForm
{
    protected PageBlockRepository $repository;

    protected PageBlockGroupRepository $groupRepository;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->repository      = app(PageBlockRepository::class);
        $this->groupRepository = app(PageBlockGroupRepository::class);
    }

    protected function initializeModel(): void
    {
        $this->model = new PageBlock();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->select('block_group_id', __t('Group'))->options(
            $this->groupRepository->all()->pluck('title', 'id')->toArray()
        )->required();
        $this->text('title', __t('Title'))->required();
        $this->text('class', __t('Class'))->required();
        $this->text('remark', __t('Remark'));
        $this->textarea('description', __t('Description'))->rows(2);
        $this->textarea('template', __t('template'))->rows(3);
        $this->textarea('instruction', __t('Instruction'))->rows(2);
        $this->textarea('schema', __t('Schema'))
            ->rows(10)
            ->help(__t('Enter JSON format for block schema'));

        // Add scope field with radio buttons
        $this->radio('scope', __t('Scope'))
            ->options([
                BlockScope::Page->value   => BlockScope::Page->toArray(),
                BlockScope::Global->value => BlockScope::Global->toArray(),
            ])
            ->default(BlockScope::Page->value)
            ->required();

        // Add query_options field as textarea
        $this->textarea('query_options', __t('Query Options'))
            ->rows(10)
            ->help(__t('Enter JSON format for query options'));

        // Add query_options_definition: schema for query_options (field types, labels, defaults)
        $this->textarea('query_options_definition', __t('Query Options Definition'))
            ->rows(10)
            ->help(__t('JSON schema defining query_options fields (type, label, default, options). Used for form generation.'));

        $this->switch('droppable', __t('Droppable'));
        $this->keyValue('setting', __t('Setting'))->default([])->setKeyLabel('Key')->setValueLabel('Value')->saveAsJson();
        $this->number('sort', __t('Sort'));
        $this->switch('status', __t('Status'));
    }
}
