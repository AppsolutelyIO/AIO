<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Enums\CouponStatus;
use Appsolutely\AIO\Enums\CouponType;
use Appsolutely\AIO\Models\Coupon;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;

final class CouponController extends AdminBaseController
{
    protected function grid(): Grid
    {
        return Grid::make(Coupon::query()->withCount('usages'), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('code', __t('Code'))->copyable()->label('primary');
            $grid->column('title', __t('Title'));
            $grid->column('type', __t('Type'))->display(function ($type) {
                return $type instanceof \BackedEnum ? $type->label() : $type;
            })->label();
            $grid->column('value', __t('Value'))->display(function () {
                if ($this->type instanceof CouponType && $this->type === CouponType::Percentage) {
                    return ($this->value / CENTS_PER_UNIT) . '%';
                }

                return format_cents($this->value);
            });
            $grid->column('usage_limit', __t('Limit'))->display(function ($limit) {
                return $limit ?? '∞';
            });
            $grid->column('usages_count', __t('Used'))->sortable();
            $grid->column('status', __t('Status'))->display(function ($status) {
                return $status instanceof \BackedEnum ? $status->label() : $status;
            })->dot([
                CouponStatus::Active->value   => 'success',
                CouponStatus::Inactive->value => 'dark',
                CouponStatus::Expired->value  => 'danger',
            ]);
            $grid->column('starts_at', __t('Starts At'))->display(column_time_format())->sortable();
            $grid->column('expires_at', __t('Expires At'))->display(column_time_format())->sortable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'code', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(2);
                $filter->like('code')->width(2);
                $filter->like('title')->width(2);
                $filter->equal('type', __t('Type'))->select(CouponType::toArray())->width(3);
                $filter->equal('status', __t('Status'))->select(CouponStatus::toArray())->width(3);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(Coupon::query(), function (Form $form) {
            $form->display('id', __t('ID'));

            $form->divider(__t('Basic'));
            $form->text('code', __t('Code'))->required()->help(__t('Unique coupon code customers will enter'));
            $form->text('title', __t('Title'))->required();
            $form->textarea('description', __t('Description'))->rows(2);

            $form->divider(__t('Discount'));
            $form->radio('type', __t('Type'))->options(CouponType::toArray())->default(CouponType::FixedAmount->value)->required();
            $form->currency('value', __t('Value'))->symbol(app_currency_symbol())->required()
                ->customFormat(subunit_to_display())
                ->saving(display_to_subunit())
                ->help(__t('For fixed: dollar amount. For percentage: enter the percentage value'));
            $form->currency('min_order_amount', __t('Min Order Amount'))->symbol(app_currency_symbol())->default(0)
                ->customFormat(subunit_to_display())
                ->saving(display_to_subunit());
            $form->currency('max_discount_amount', __t('Max Discount'))->symbol(app_currency_symbol())->default(0)
                ->customFormat(subunit_to_display())
                ->saving(display_to_subunit())
                ->help(__t('Max discount cap for percentage coupons (0 = no cap)'));

            $form->divider(__t('Limits'));
            $form->number('usage_limit', __t('Usage Limit'))->help(__t('Leave empty for unlimited'))->default(null);
            $form->number('usage_per_user', __t('Per User Limit'))->help(__t('Leave empty for unlimited'))->default(null);
            $form->display('used_count', __t('Used Count'));

            $form->divider(__t('Schedule'));
            $form->select('status', __t('Status'))->options(CouponStatus::toArray())->default(CouponStatus::Active->value);
            $form->datetime('starts_at', __t('Starts At'));
            $form->datetime('expires_at', __t('Expires At'));

            $form->disableViewButton();
            $form->disableViewCheck();
        });
    }
}
