<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Admin\Forms\NestedSetForm as Form;
use Appsolutely\AIO\Models\ArticleCategory;
use Appsolutely\AIO\Repositories\ArticleCategoryRepository;
use Appsolutely\AIO\Form\BlockForm;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Layout\Content;
use Appsolutely\AIO\Layout\Row;
use Appsolutely\AIO\Tree;
use Illuminate\Support\Facades\Request;

final class ArticleCategoryController extends AdminBaseController
{
    public function __construct(protected ArticleCategoryRepository $articleCategoryRepository) {}

    public function index1(Content $content)
    {

        return $content
            ->header('Article Categories')
            ->body(function (Row $row) {
                $tree = new Tree(new ArticleCategory());
                $tree->disableSaveButton();
                $row->column(12, $tree);
            });
    }

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new ArticleCategory(), function (Grid $grid) {
            $grid->column('id', __t('ID'))->width('50px');
            $grid->column('title', __t('Title'))->tree(true)->width('400px');
            $grid->column('children_count', __t('Children'))
                ->width('80px')->setAttributes(children_attributes());
            $grid->column('status', __t('Status'))->switch();
            $grid->column('slug', __t('Slug'))->textarea()->width('240px');
            $grid->order->orderable();
            $grid->model()->withCount('children')->orderBy('left', 'ASC');

            $grid->enableDialogCreate();
            $grid->setDialogFormDimensions('85%', '85%');

            $grid->quickSearch('id', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(3);
                $filter->like('title')->width(3);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
            });
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new ArticleCategory(), function (Form $form) {
            $form->block(7, function (BlockForm $form) {
                $form->title(__('Basic'));

                $form->display('id', __t('ID'));

                $availableCategories = $this->articleCategoryRepository->getActiveList();
                $form->select('parent_id', 'Parent')->options($availableCategories);

                $form->text('title', __t('Title'))->required();
                $form->text('slug', __t('Slug'));

                $form->textarea('keywords', __t('Keywords'))->rows(3);
                $form->textarea('description', __t('Description'))->rows();
            });

            $form->block(5, function (BlockForm $form) {
                $form->title('Optional');
                $form->keyValue('setting', __t('Setting'))->default([])->setKeyLabel('Key')->setValueLabel('Value')->saveAsJson();
                $form->image('cover', __t('Cover'))->autoUpload()->url(upload_to_api(ArticleCategory::class, $form->getKey()));
                $form->switch('status', __t('Status'));
                $form->showFooter();
            });
            $form->saving(function (Form $form) {
                /** @var ArticleCategory $model */
                $model = $form->model();

                if (Request::has('parent_id')) {
                    $parentId = $form->input('parent_id');
                    $parent   = $model->find($parentId);
                    if ($parent) {
                        $model->appendToNode($parent)->save();
                    } else {
                        $model->saveAsRoot();
                    }
                } elseif (Request::has('_orderable')) {
                    $moveUp = $form->input('_orderable') == 1;
                    $node   = $model->find($form->getKey());
                    if ($moveUp) {
                        $node->up();
                    } else {
                        $node->down();
                    }
                } else {
                    $model->save();
                }

                return $model;
            });
        });
    }
}
