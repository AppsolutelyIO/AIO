<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Enums\OrderPaymentStatus;
use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\OrderShipment;
use Appsolutely\AIO\Models\Refund;
use Appsolutely\AIO\Services\OrderService;

final class OrderController extends AdminBaseController
{
    public function __construct(protected OrderService $orderService) {}

    protected function grid(): Grid
    {
        return Grid::make(
            Order::with(['user'])->withCount(['items', 'refunds']),
            function (Grid $grid) {
                $grid->column('id', __t('ID'))->sortable();
                $grid->column('reference', __t('Reference'));
                $grid->column('user.name', __t('User'));
                $grid->column('summary', __t('Summary'))->limit(40);
                $grid->column('items_count', __t('Items'));
                $grid->column('total_amount', __t('Total'))->display(fn ($v) => format_cents($v))->sortable();
                $grid->column('refunds_count', __t('Refunds'))->display(function ($count) {
                    return $count > 0 ? "<span class='label bg-warning'>{$count}</span>" : '0';
                });
                $grid->column('status', __t('Status'))->display(function ($status) {
                    return $status instanceof \BackedEnum ? $status->label() : $status;
                })->dot([
                    OrderStatus::Pending->value   => 'warning',
                    OrderStatus::Paid->value      => 'primary',
                    OrderStatus::Shipped->value   => 'info',
                    OrderStatus::Completed->value => 'success',
                    OrderStatus::Cancelled->value => 'danger',
                ]);
                $grid->column('created_at', __t('Created At'))->display(column_time_format())->sortable();
                $grid->model()->orderByDesc('id');

                $grid->quickSearch('id', 'reference', 'user_id');
                $grid->filter(function (Grid\Filter $filter) {
                    $filter->equal('id')->width(2);
                    $filter->like('reference')->width(2);
                    $filter->equal('user_id', __t('User ID'))->width(2);
                    $filter->equal('status', __t('Status'))->select(OrderStatus::toArray())->width(2);
                    $filter->between('created_at', __t('Created At'))->datetime()->width(3);
                });

                $grid->actions(function (Grid\Displayers\Actions $actions) {
                    $actions->disableView();
                    $actions->disableDelete();
                });

                $grid->disableCreateButton();
            }
        );
    }

    protected function form(): Form
    {
        return Form::make(Order::with(['user', 'coupon']), function (Form $form) {
            $form->defaultEditingChecked();

            $form->tab(__t('Details'), function (Form $form) {
                $this->detailsTab($form);
            }, true, 'details')->tab(__t('Items'), function (Form $form) {
                $form->html($this->itemsGrid($form->getKey()));
            }, false, 'items')->tab(__t('Payments'), function (Form $form) {
                $form->html($this->paymentsGrid($form->getKey()));
            }, false, 'payments')->tab(__t('Shipments'), function (Form $form) {
                $form->html($this->shipmentsGrid($form->getKey()));
            }, false, 'shipments')->tab(__t('Refunds'), function (Form $form) {
                $form->html($this->refundsGrid($form->getKey()));
            }, false, 'refunds');
        });
    }

    protected function detailsTab(Form $form): void
    {
        $form->display('id', __t('ID'));
        $form->display('reference', __t('Reference'));
        $form->display('user.name', __t('User'));
        $form->display('summary', __t('Summary'));

        $form->divider(__t('Amounts'));
        $form->currency('amount', __t('Amount'))->symbol(app_currency_symbol())->readOnly()
            ->customFormat(subunit_to_display());
        $form->currency('discounted_amount', __t('Discount'))->symbol(app_currency_symbol())->readOnly()
            ->customFormat(subunit_to_display());
        $form->currency('total_amount', __t('Total'))->symbol(app_currency_symbol())->readOnly()
            ->customFormat(subunit_to_display());

        $form->divider(__t('Status'));
        $form->select('status', __t('Status'))->options(function () use ($form) {
            $currentStatus = $form->model()?->status;
            if (! $currentStatus instanceof OrderStatus) {
                return OrderStatus::toArray();
            }

            return collect(OrderStatus::toArray())->filter(function ($label, $value) use ($currentStatus, $form) {
                return $value === $currentStatus->value
                    || $this->orderService->canTransitionTo($form->model(), OrderStatus::from($value));
            })->toArray();
        });

        $form->divider(__t('Extra'));
        $form->display('coupon.code', __t('Coupon Code'));
        $form->textarea('delivery_info', __t('Delivery Info'))->rows(3)->readOnly();
        $form->textarea('note', __t('Note'))->rows(2);
        $form->textarea('remark', __t('Remark'))->rows(2);
        $form->display('ip', __t('IP'));
        $form->display('created_at', __t('Created At'));
        $form->display('updated_at', __t('Updated At'));

        $form->disableViewButton();
        $form->disableViewCheck();
        $form->disableDeleteButton();
    }

    protected function itemsGrid(?int $orderId): Grid
    {
        return Grid::make(OrderItem::with(['product', 'productSku']), function (Grid $grid) use ($orderId) {
            $grid->model()->where('order_id', $orderId ?? 0);

            $grid->column('id', __t('ID'))->sortable();
            $grid->column('reference', __t('Reference'));
            $grid->column('product.title', __t('Product'))->display(function ($title) {
                $productId = $this->product_id;
                $url       = admin_route('products.entry.edit', [$productId]);

                return "<a href='{$url}'>{$title}</a>";
            });
            $grid->column('productSku.title', __t('SKU'));
            $grid->column('original_price', __t('Original Price'))->display(fn ($v) => format_cents($v));
            $grid->column('price', __t('Price'))->display(fn ($v) => format_cents($v));
            $grid->column('quantity', __t('Qty'));
            $grid->column('amount', __t('Amount'))->display(fn ($v) => format_cents($v));
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->label();

            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disablePagination();
            $grid->disableRowSelector();
        });
    }

    protected function paymentsGrid(?int $orderId): Grid
    {
        return Grid::make(OrderPayment::query(), function (Grid $grid) use ($orderId) {
            $grid->model()->where('order_id', $orderId ?? 0);

            $grid->column('id', __t('ID'));
            $grid->column('reference', __t('Reference'));
            $grid->column('vendor', __t('Vendor'))->label();
            $grid->column('vendor_reference', __t('Vendor Ref'));
            $grid->column('payment_amount', __t('Amount'))->display(fn ($v) => format_cents($v));
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                OrderPaymentStatus::Pending->value  => 'warning',
                OrderPaymentStatus::Paid->value     => 'success',
                OrderPaymentStatus::Failed->value   => 'danger',
                OrderPaymentStatus::Refunded->value => 'info',
            ]);
            $grid->column('created_at', __t('Created At'))->display(column_time_format());

            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disablePagination();
            $grid->disableRowSelector();
        });
    }

    protected function shipmentsGrid(?int $orderId): Grid
    {
        return Grid::make(OrderShipment::query(), function (Grid $grid) use ($orderId) {
            $grid->model()->where('order_id', $orderId ?? 0);

            $grid->column('id', __t('ID'));
            $grid->column('name', __t('Name'));
            $grid->column('address', __t('Address'));
            $grid->column('city', __t('City'));
            $grid->column('country', __t('Country'));
            $grid->column('delivery_vendor', __t('Vendor'))->label();
            $grid->column('delivery_reference', __t('Tracking'));
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                OrderShipmentStatus::Pending->value   => 'warning',
                OrderShipmentStatus::Shipped->value   => 'info',
                OrderShipmentStatus::Delivered->value => 'success',
            ]);

            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disablePagination();
            $grid->disableRowSelector();
        });
    }

    protected function refundsGrid(?int $orderId): Grid
    {
        return Grid::make(Refund::with(['user']), function (Grid $grid) use ($orderId) {
            $grid->model()->where('order_id', $orderId ?? 0);

            $grid->column('id', __t('ID'));
            $grid->column('reference', __t('Reference'));
            $grid->column('user.name', __t('User'));
            $grid->column('amount', __t('Amount'))->display(fn ($v) => format_cents($v));
            $grid->column('reason', __t('Reason'))->limit(40);
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                RefundStatus::Pending->value  => 'warning',
                RefundStatus::Approved->value => 'primary',
                RefundStatus::Rejected->value => 'danger',
                RefundStatus::Refunded->value => 'success',
            ]);
            $grid->column('created_at', __t('Created At'))->display(column_time_format());

            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disablePagination();
            $grid->disableRowSelector();
        });
    }
}
