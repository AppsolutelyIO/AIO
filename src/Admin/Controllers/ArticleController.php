<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Repositories\ArticleCategoryRepository;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Illuminate\Support\HtmlString;

final class ArticleController extends AdminBaseController
{
    public function __construct(
        protected ArticleCategoryRepository $articleCategoryRepository,
    ) {}

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        $controller = $this;

        return Grid::make(Article::with(['categories.ancestors']), function (Grid $grid) use ($controller) {

            $grid->column('id', __t('ID'))->sortable();

            $grid->column('title', __t('Title'))->display(fn ($value) => new HtmlString(truncate($value)))->tooltip()->editable();

            $grid->column('urls', __t('Links'))->display(function () use ($controller) {
                return $controller->buildSlugUrls($this);
            });

            $grid->column('categories', __t('Categories'))->pluck('title')->label();

            $grid->column('published_at', __t('Published At'))->display(column_time_format())->sortable();
            $grid->column('expired_at', __t('Expired At'))->display(column_time_format())->sortable();
            $grid->column('status', __t('Status'))->switch();
            $grid->column('sort', __t('Sort'))->editable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('title')->width(3);
                $filter->like('content')->width(3);
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
        return Form::make(Article::with(['categories']), function (Form $form) {
            $form->defaultEditingChecked();

            $form->tab('Basic', function (Form $form) {
                $form->display('id', __t('ID'));

                $availableCategories = $this->articleCategoryRepository->getActiveList();
                $form->multipleSelect('categories', 'Categories')->required()->options($availableCategories)
                    ->customFormat(extract_values());

                $form->text('title', __t('Title'))->required();
                $form->text('slug', __t('Slug'));

                $form->vditor('content', __t('Content'))->required();
                $form->datetime('published_at', __t('Published At'));
                $form->datetime('expired_at', __t('Expired At'));
                $form->switch('status', __t('Status'));

            })->tab('Optional', function (Form $form) {
                $form->image('cover', __t('Cover'))->autoUpload()->url(upload_to_api(Article::class, $form->getKey()));
                $form->textarea('keywords', __t('Keywords'))->rows(2);
                $form->textarea('description', __t('Description'))->rows(2);
            })->tab('Setting', function (Form $form) {
                $form->keyValue('setting', __t('Setting'))->default(Article::defaultSetting(), true)->setKeyLabel('Key')->setValueLabel('Value')->saveAsJson();
            });
        });
    }
}
