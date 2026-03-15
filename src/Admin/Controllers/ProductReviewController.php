<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Enums\ReviewStatus;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Models\ProductReview;

final class ProductReviewController extends AdminBaseController
{
    protected function grid(): Grid
    {
        return Grid::make(ProductReview::with(['product', 'user', 'order']), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('product.title', __t('Product'))->display(function ($title) {
                $productId = $this->product_id;
                $url       = admin_route('products.entry.edit', [$productId]);

                return "<a href='{$url}'>{$title}</a>";
            });
            $grid->column('user.name', __t('User'));
            $grid->column('order.reference', __t('Order'))->display(function ($reference) {
                if (! $reference || ! $this->order_id) {
                    return '—';
                }

                $url = admin_route('orders.entry.edit', [$this->order_id]);

                return "<a href='{$url}'>{$reference}</a>";
            });
            $grid->column('rating', __t('Rating'))->display(function ($rating) {
                return str_repeat('★', (int) $rating) . str_repeat('☆', 5 - (int) $rating);
            });
            $grid->column('title', __t('Title'))->limit(40);
            $grid->column('body', __t('Review'))->limit(60);
            $grid->column('verified_at', __t('Verified'))->bool();
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                ReviewStatus::Pending->value  => 'warning',
                ReviewStatus::Approved->value => 'success',
                ReviewStatus::Rejected->value => 'danger',
            ]);
            $grid->column('created_at', __t('Created At'))->display(column_time_format())->sortable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'title', 'body');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(2);
                $filter->equal('product_id', __t('Product ID'))->width(2);
                $filter->equal('user_id', __t('User ID'))->width(2);
                $filter->equal('rating', __t('Rating'))->select([1 => '1 ★', 2 => '2 ★★', 3 => '3 ★★★', 4 => '4 ★★★★', 5 => '5 ★★★★★'])->width(2);
                $filter->equal('status', __t('Status'))->select(ReviewStatus::toArray())->width(2);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableDelete();
            });

            $grid->disableCreateButton();
        });
    }

    protected function form(): Form
    {
        return Form::make(ProductReview::with(['product', 'user', 'order']), function (Form $form) {
            $form->display('id', __t('ID'));

            $form->divider(__t('Review'));
            $form->display('product.title', __t('Product'));
            $form->display('user.name', __t('User'));
            $form->display('order.reference', __t('Order'));
            $form->display('rating', __t('Rating'));
            $form->display('title', __t('Title'));
            $form->textarea('body', __t('Review'))->rows(4)->readOnly();

            $form->divider(__t('Moderation'));
            $form->select('status', __t('Status'))->options(ReviewStatus::toArray())->required();
            $form->datetime('verified_at', __t('Verified At'))
                ->help(__t('Set to mark this review as a verified purchase'));

            $form->divider(__t('Timestamps'));
            $form->display('created_at', __t('Created At'));
            $form->display('updated_at', __t('Updated At'));

            $form->disableViewButton();
            $form->disableViewCheck();
            $form->disableDeleteButton();
        });
    }
}
