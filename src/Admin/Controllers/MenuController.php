<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Enums\MenuTarget;
use Appsolutely\AIO\Enums\MenuType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Menu;
use Appsolutely\AIO\Repositories\MenuRepository;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Illuminate\Support\Facades\Request;

final class MenuController extends AdminBaseController
{
    public function __construct(protected MenuRepository $menuRepository) {}

    protected function grid(): Grid
    {
        return Grid::make(Menu::class, function (Grid $grid) {
            $grid->column('id', __t('ID'))->width('50px');
            $grid->column('title', __t('Title'))->tree(true)->width('300px');
            $grid->column('menu.title', __t('Menu'));
            $grid->column('children_count', __t('Children'))
                ->width('50px')->setAttributes(children_attributes());
            $grid->column('url', __t('Url'))->width('200px');
            $grid->column('target', __t('Target'))->display(function ($target) {
                return $target->toArray();
            });
            $grid->column('published_at', __t('Published At'))->display(column_time_format())->sortable();
            $grid->column('expired_at', __t('Expired At'))->display(column_time_format())->sortable();
            $grid->column('status', __t('Status'))->switch();
            $grid->order->orderable();
            $grid->model()->withCount('children')->orderBy('order', 'ASC');

            $grid->quickSearch('id', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(3);
                $filter->like('title')->width(3);
                $filter->equal('type', __t('Type'))->select(enum_options(MenuType::class))->width(3);
                $filter->equal('status')->width(3);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(Menu::class, function (Form $form) {
            $form->disableViewButton();
            $form->disableViewCheck();
            $form->display('id', __t('ID'));

            $menuItems = $this->menuRepository->getActiveList();
            $form->select('parent_id', __t('Parent Item'))
                ->options($menuItems)
                ->help(__t('Leave empty for root menu'));

            $form->text('title', __t('Title'))->required();
            $form->text('remark', __t('Remark'));
            $form->text('url', __t('Url'));

            $form->radio('type', __t('Type'))
                ->options(enum_options(MenuType::class))
                ->default(MenuType::Link->value)
                ->required();

            $form->text('icon', __t('Icon'))->help(__t('CSS class or icon name'));
            $form->text('thumbnail', __t('Thumbnail'))->help(__t('Menu item thumbnail image'));
            $form->keyValue('setting', __t('Settings'))->help(__t('Additional settings in key-value format'));
            $form->text('permission_key', __t('Permission Key'));

            $form->radio('target', __t('Target'))
                ->options(enum_options(MenuTarget::class))
                ->default(MenuTarget::Self->value);

            $form->switch('is_external', __t('External Link'))->default(false);

            $form->datetime('published_at', __t('Published At (%s)', [app_local_timezone()]));
            $form->datetime('expired_at', __t('Expired At (%s)', [app_local_timezone()]));

            $form->switch('status', __t('Status'))->default(Status::ACTIVE->value);

            $form->saving(function (Form $form) {
                /** @var Menu $model */
                $model    = $form->model();
                $parentId = $form->input('parent_id');

                $isChildItem = $parentId && Menu::find($parentId);

                if ($isChildItem) {
                    $parent = Menu::find($parentId);
                    $model->appendToNode($parent)->save();
                } else {
                    $model->saveAsRoot();
                }

                // Handle orderable operations
                if (Request::has('_orderable')) {
                    $moveUp = $form->input('_orderable') == 1;
                    $node   = $model->find($form->getKey());
                    if ($moveUp) {
                        $node->up();
                    } else {
                        $node->down();
                    }
                }

                return $model;
            });
        });
    }
}
