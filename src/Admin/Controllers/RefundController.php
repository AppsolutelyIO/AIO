<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Models\Refund;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;

final class RefundController extends AdminBaseController
{
    protected function grid(): Grid
    {
        return Grid::make(Refund::with(['order', 'user', 'orderPayment']), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('reference', __t('Reference'));
            $grid->column('order.reference', __t('Order'))->display(function ($reference) {
                $orderId = $this->order_id;
                $url     = admin_route('orders.entry.edit', [$orderId]);

                return "<a href='{$url}'>{$reference}</a>";
            });
            $grid->column('user.name', __t('User'));
            $grid->column('orderPayment.vendor', __t('Payment'))->label();
            $grid->column('amount', __t('Amount'))->display(fn ($v) => format_cents($v))->sortable();
            $grid->column('reason', __t('Reason'))->limit(50);
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                RefundStatus::Pending->value  => 'warning',
                RefundStatus::Approved->value => 'primary',
                RefundStatus::Rejected->value => 'danger',
                RefundStatus::Refunded->value => 'success',
            ]);
            $grid->column('created_at', __t('Created At'))->display(column_time_format())->sortable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'reference');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(2);
                $filter->like('reference')->width(2);
                $filter->equal('order_id', __t('Order ID'))->width(2);
                $filter->equal('status', __t('Status'))->select(RefundStatus::toArray())->width(2);
                $filter->between('created_at', __t('Created At'))->datetime()->width(3);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableDelete();
            });

            $grid->disableCreateButton();

            $grid->batchActions(function (Grid\Tools\BatchActions $actions) {
                $actions->add(new Grid\BatchAction(__t('Approve Selected')));
                $actions->add(new Grid\BatchAction(__t('Reject Selected')));
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(Refund::with(['order', 'user', 'orderPayment']), function (Form $form) {
            $form->display('id', __t('ID'));
            $form->display('reference', __t('Reference'));
            $form->display('order.reference', __t('Order'));
            $form->display('user.name', __t('User'));
            $form->display('orderPayment.vendor', __t('Payment Vendor'));

            $form->divider(__t('Refund Details'));
            $form->currency('amount', __t('Amount'))->symbol(app_currency_symbol())->readOnly()
                ->customFormat(subunit_to_display());
            $form->textarea('reason', __t('Reason'))->rows(3)->readOnly();

            $form->divider(__t('Processing'));
            $form->select('status', __t('Status'))->options(RefundStatus::toArray())->required();
            $form->textarea('admin_note', __t('Admin Note'))->rows(3)
                ->help(__t('Internal note for tracking the refund decision'));
            $form->text('vendor_reference', __t('Vendor Reference'))
                ->help(__t('Payment gateway refund reference'));

            $form->divider(__t('Timestamps'));
            $form->display('refunded_at', __t('Refunded At'));
            $form->display('created_at', __t('Created At'));
            $form->display('updated_at', __t('Updated At'));

            $form->disableViewButton();
            $form->disableViewCheck();
            $form->disableDeleteButton();
        });
    }
}
