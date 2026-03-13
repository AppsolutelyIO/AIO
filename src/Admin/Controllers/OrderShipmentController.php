<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Models\OrderShipment;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;

final class OrderShipmentController extends AdminBaseController
{
    protected function grid(): Grid
    {
        return Grid::make(OrderShipment::with(['order']), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('order.reference', __t('Order'))->display(function ($reference) {
                $orderId = $this->order_id;
                $url     = admin_route('orders.entry.edit', [$orderId]);

                return "<a href='{$url}'>{$reference}</a>";
            });
            $grid->column('name', __t('Recipient'));
            $grid->column('city', __t('City'));
            $grid->column('country', __t('Country'));
            $grid->column('delivery_vendor', __t('Vendor'))->label();
            $grid->column('delivery_reference', __t('Tracking'))->copyable();
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                OrderShipmentStatus::Pending->value   => 'warning',
                OrderShipmentStatus::Shipped->value   => 'info',
                OrderShipmentStatus::Delivered->value => 'success',
            ]);
            $grid->column('created_at', __t('Created At'))->display(column_time_format())->sortable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'name', 'delivery_reference');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(2);
                $filter->equal('order_id', __t('Order ID'))->width(2);
                $filter->like('name', __t('Recipient'))->width(2);
                $filter->like('delivery_reference', __t('Tracking'))->width(2);
                $filter->equal('status', __t('Status'))->select(OrderShipmentStatus::toArray())->width(2);
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
        return Form::make(OrderShipment::with(['order']), function (Form $form) {
            $form->display('id', __t('ID'));
            $form->display('order.reference', __t('Order'));

            $form->divider(__t('Recipient'));
            $form->display('name', __t('Name'));
            $form->display('email', __t('Email'));
            $form->display('mobile', __t('Mobile'));

            $form->divider(__t('Address'));
            $form->display('address', __t('Address'));
            $form->display('address_extra', __t('Address Line 2'));
            $form->display('town', __t('Town'));
            $form->display('city', __t('City'));
            $form->display('province', __t('Province'));
            $form->display('postcode', __t('Postcode'));
            $form->display('country', __t('Country'));

            $form->divider(__t('Delivery'));
            $form->select('status', __t('Status'))->options(OrderShipmentStatus::toArray())->required();
            $form->text('delivery_vendor', __t('Delivery Vendor'))
                ->help(__t('e.g. ups, fedex, dhl'));
            $form->text('delivery_reference', __t('Tracking Number'));
            $form->textarea('remark', __t('Remark'))->rows(2);

            $form->disableViewButton();
            $form->disableViewCheck();
            $form->disableDeleteButton();
        });
    }
}
