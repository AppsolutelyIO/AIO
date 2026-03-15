<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Admin\Actions\Grid\DeleteAction;
use Appsolutely\AIO\Admin\Forms\Models\NotificationQueueForm;
use Appsolutely\AIO\Admin\Forms\Models\NotificationRuleForm;
use Appsolutely\AIO\Admin\Forms\Models\NotificationSenderForm;
use Appsolutely\AIO\Admin\Forms\Models\NotificationTemplateForm;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Grid\Tools;
use Appsolutely\AIO\Helpers\AdminButtonHelper;
use Appsolutely\AIO\Layout\Content;
use Appsolutely\AIO\Layout\Row;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;
use Appsolutely\AIO\Services\NotificationRuleService;
use Appsolutely\AIO\Services\NotificationService;
use Appsolutely\AIO\Services\NotificationTemplateService;
use Appsolutely\AIO\Widgets\Modal;
use Appsolutely\AIO\Widgets\Tab;

final class NotificationController extends AdminBaseController
{
    public function __construct(
        protected NotificationTemplateRepository $templateRepository,
        protected NotificationRuleRepository $ruleRepository,
        protected NotificationQueueRepository $queueRepository,
        protected NotificationService $notificationService,
        protected NotificationTemplateService $templateService,
        protected NotificationRuleService $ruleService
    ) {}

    public function index(Content $content): Content
    {
        return $content
            ->header(__t('Notifications'))
            ->description(__t('Manage email templates, rules, and queue'))
            ->body($this->buildTabs());
    }

    protected function buildTabs(): Tab
    {
        $tab = new Tab();

        $queueContent = new Row();
        $queueContent->column(12, $this->statisticsGrid());
        $queueContent->column(12, $this->queueGrid());

        $tab->add(__t('Queue'), $queueContent, true, 'queue');
        $tab->add(__t('Rules'), $this->rulesGrid(), false, 'rules');
        $tab->add(__t('Templates'), $this->templatesGrid(), false, 'templates');
        $tab->add(__t('Senders'), $this->sendersGrid(), false, 'senders');

        $tab->withCard();

        return $tab;
    }

    protected function templatesGrid(): Grid
    {
        return Grid::make(new NotificationTemplate(), function (Grid $grid) {
            admin_script(AdminButtonHelper::scriptForApiButton([
                'function_name'   => 'duplicateItem',
                'api_url'         => admin_route('api.notifications.duplicate-template', ['id' => '__ID__']),
                'success_message' => __t('Item duplicated successfully'),
                'error_message'   => __t('Failed to duplicate item'),
            ]));

            $grid->model()->orderBy('created_at', 'desc');

            $grid->column('id', __t('ID'))->sortable();
            $grid->column('name', __t('Name'))->limit(50);
            $grid->column('category', __t('Category'))->label();
            $grid->column('subject', __t('Subject'))->limit(60);
            $grid->column('usage_count', __t('Rules'))->display(function ($count) {
                return "<span class='badge bg-info'>{$count}</span>";
            });
            $grid->column('status', __t('Status'))->switch();
            $grid->column('created_at', __t('Created'))->display(column_time_format())->sortable();

            $grid->quickSearch('id', 'name', 'subject');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', __t('ID'))->width(3);
                $filter->like('name', __t('Name'))->width(3);
                $filter->equal('category', __t('Category'))->width(3);
                $filter->equal('status', __t('Status'))->select(Status::toTranslatedArray())->width(3);
            });

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableExport();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(Modal::make()->xl()->scrollable()
                    ->title(__t('Edit Template') . ' #' . $actions->getKey())
                    ->body(NotificationTemplateForm::make($actions->getKey())->payload([
                        'id' => $actions->getKey(),
                    ]))
                    ->button(admin_edit_action()));

                if (! $actions->row->is_system) {
                    $id = $actions->getKey();
                    $actions->append(AdminButtonHelper::apiButton([
                        'text'          => __t('Duplicate'),
                        'icon'          => 'fa fa-copy',
                        'class'         => 'text-info',
                        'function_name' => 'duplicateItem',
                        'api_url'       => admin_route('api.notifications.duplicate-template', ['id' => '__ID__']),
                        'payload'       => $id,
                    ]));
                }

                $actions->append(new DeleteAction());
            });

            $grid->tools(function (Tools $tools) {
                $tools->append(
                    Modal::make()->xl()->scrollable()
                        ->title(__t('New Template'))
                        ->body(NotificationTemplateForm::make())
                        ->button(admin_create_button())
                );
            });
        });
    }

    protected function rulesGrid(): Grid
    {
        return Grid::make(NotificationRule::with('template'), function (Grid $grid) {
            admin_script(AdminButtonHelper::scriptForApiButton([
                'function_name' => 'testNotificationRule',
                'api_url'       => admin_route('api.notifications.test-rule', ['id' => '__ID__']),
                'refresh'       => false,
                'error_message' => __t('Failed to test rule'),
            ]));

            $grid->model()->orderBy('created_at', 'desc');

            $grid->column('id', __t('ID'))->sortable();
            $grid->column('name', __t('Name'))->limit(50);
            $grid->column('trigger_type', __t('Trigger'))->label();
            $grid->column('trigger_reference', __t('Reference'))->limit(30);
            $grid->column('template.name', __t('Template'))->limit(40);
            $grid->column('recipient_type', __t('Recipients'))->display(function ($type) {
                $labels = [
                    'admin'       => __t('Admin'),
                    'user'        => __t('User'),
                    'custom'      => __t('Custom'),
                    'conditional' => __t('Conditional'),
                ];

                return "<span class='badge bg-secondary'>{$labels[$type]}</span>";
            });
            $grid->column('delay_minutes', __t('Delay'))->display(function ($minutes) {
                return $minutes > 0 ? "{$minutes}m" : __t('Immediate');
            });
            $grid->column('status', __t('Status'))->switch();
            $grid->column('created_at', __t('Created'))->display(column_time_format())->sortable();

            $grid->quickSearch('id', 'name', 'trigger_reference');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', __t('ID'))->width(3);
                $filter->like('name', __t('Name'))->width(3);
                $filter->equal('trigger_type', __t('Trigger'))->width(3);
                $filter->equal('status', __t('Status'))->select(Status::toTranslatedArray())->width(3);
            });

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableExport();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(Modal::make()->xl()->scrollable()
                    ->title(__t('Edit Rule') . ' #' . $actions->getKey())
                    ->body(NotificationRuleForm::make($actions->getKey())->payload([
                        'id' => $actions->getKey(),
                    ]))
                    ->button(admin_edit_action()));

                $id = $actions->getKey();
                $actions->append(AdminButtonHelper::apiButton([
                    'text'          => __t('Test'),
                    'icon'          => 'fa fa-flask',
                    'class'         => '',
                    'function_name' => 'testNotificationRule',
                    'api_url'       => admin_route('api.notifications.test-rule', ['id' => '__ID__']),
                    'payload'       => $id,
                ]));
                $actions->append(new DeleteAction());
            });

            $grid->tools(function (Tools $tools) {
                $tools->append(
                    Modal::make()->xl()->scrollable()
                        ->title(__t('New Rule'))
                        ->body(NotificationRuleForm::make())
                        ->button(admin_create_button())
                );
            });
        });
    }

    protected function sendersGrid(): Grid
    {
        return Grid::make(NotificationSender::query(), function (Grid $grid) {
            $grid->model()->orderBy('category')->orderBy('priority', 'desc')->orderBy('name');

            $grid->column('id', __t('ID'))->sortable();
            $grid->column('name', __t('Name'))->limit(50);
            $grid->column('slug', __t('Slug'))->limit(30);
            $grid->column('category', __t('Category'))->display(function ($category) {
                $labels = [
                    'internal' => __t('Internal'),
                    'external' => __t('External'),
                    'system'   => __t('System'),
                ];
                $colors = [
                    'internal' => 'primary',
                    'external' => 'success',
                    'system'   => 'warning',
                ];

                return "<span class='badge bg-{$colors[$category]}'>{$labels[$category]}</span>";
            });
            $grid->column('type', __t('Type'))->label();
            $grid->column('from_address', __t('From Address'))->limit(40);
            $grid->column('is_default', __t('Default'))->bool();
            $grid->column('priority', __t('Priority'))->sortable();
            $grid->column('is_active', __t('Active'))->bool();
            $grid->column('usage_stats', __t('Usage'))->display(function () {
                $stats = $this->usage_stats;

                return "<span class='badge bg-info'>{$stats['rules_count']} rules</span> " .
                       "<span class='badge bg-success'>{$stats['sent_count']} sent</span>";
            });
            $grid->column('created_at', __t('Created'))->display(column_time_format())->sortable();

            $grid->quickSearch('id', 'name', 'slug', 'from_address');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', __t('ID'))->width(3);
                $filter->like('name', __t('Name'))->width(3);
                $filter->equal('category', __t('Category'))
                    ->select([
                        'internal' => __t('Internal'),
                        'external' => __t('External'),
                        'system'   => __t('System'),
                    ])->width(3);
                $filter->equal('type', __t('Type'))
                    ->select([
                        'smtp'     => 'SMTP',
                        'sendmail' => 'Sendmail',
                        'mailgun'  => 'Mailgun',
                        'ses'      => 'Amazon SES',
                        'postmark' => 'Postmark',
                        'resend'   => 'Resend',
                        'log'      => 'Log',
                    ])->width(3);
                $filter->equal('is_active', __t('Active'))
                    ->select([Status::ACTIVE->value => __t('Yes'), Status::INACTIVE->value => __t('No')])->width(3);
            });

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableExport();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(Modal::make()->xl()->scrollable()
                    ->title(__t('Edit Sender') . ' #' . $actions->getKey())
                    ->body(NotificationSenderForm::make($actions->getKey())->payload([
                        'id' => $actions->getKey(),
                    ]))
                    ->button(admin_edit_action()));

                $actions->append(new DeleteAction());
            });

            $grid->tools(function (Tools $tools) {
                $tools->append(
                    Modal::make()->xl()->scrollable()
                        ->title(__t('New Sender'))
                        ->body(NotificationSenderForm::make())
                        ->button(admin_create_button())
                );
            });
        });
    }

    protected function queueGrid(): Grid
    {
        return Grid::make(NotificationQueue::with(['template', 'rule']), function (Grid $grid) {
            admin_script(AdminButtonHelper::scriptForApiButton([
                'function_name'   => 'retryNotification',
                'api_url'         => admin_route('api.notifications.retry', ['id' => '__ID__']),
                'confirm'         => __t('Are you sure you want to retry this notification?'),
                'success_message' => __t('Notification queued for retry'),
                'error_message'   => __t('Failed to retry notification'),
            ]));
            admin_script(AdminButtonHelper::scriptForApiButton([
                'function_name'   => 'cancelNotification',
                'api_url'         => admin_route('api.notifications.cancel', ['id' => '__ID__']),
                'confirm'         => __t('Are you sure you want to cancel this notification?'),
                'success_message' => __t('Notification cancelled'),
                'error_message'   => __t('Failed to cancel notification'),
            ]));

            $grid->model()->orderBy('created_at', 'desc');

            $grid->column('id', __t('ID'))->sortable();
            $grid->column('template.name', __t('Template'))->limit(40);
            $grid->column('recipient_email', __t('Recipient'))->limit(50);
            $grid->column('subject', __t('Subject'))->limit(60);
            $grid->column('status', __t('Status'))->display(function () {
                return $this->status_badge;
            });
            $grid->column('scheduled_at', __t('Scheduled'))->display(column_time_format())->sortable();
            $grid->column('sent_at', __t('Sent'))->display(column_time_format())->sortable();
            $grid->column('attempts', __t('Attempts'))->display(function ($attempts) {
                return $attempts > 0 ? "<span class='badge bg-warning'>{$attempts}</span>" : '-';
            });

            $grid->quickSearch('id', 'recipient_email', 'subject');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', __t('ID'))->width(3);
                $filter->like('recipient_email', __t('Recipient'))->width(3);
                $filter->equal('status', __t('Status'))->select(
                    NotificationQueue::getStatusLabels()
                )->width(3);
                $filter->between('created_at', __t('Created'))->datetime()->width(3);
            });

            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableExport();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(Modal::make()->lg()->scrollable()
                    ->title(__t('View Queue Item') . ' #' . $actions->getKey())
                    ->body(NotificationQueueForm::make($actions->getKey())->payload([
                        'id' => $actions->getKey(),
                    ]))
                    ->button(admin_edit_action()));

                $buttons = [];
                $id      = $actions->getKey();

                if ($actions->row->status === 'failed') {
                    $buttons[] = AdminButtonHelper::apiButton([
                        'text'          => __t('Retry'),
                        'icon'          => 'fa fa-refresh',
                        'class'         => 'text-warning',
                        'function_name' => 'retryNotification',
                        'api_url'       => admin_route('api.notifications.retry', ['id' => '__ID__']),
                        'payload'       => $id,
                    ]);
                }
                if ($actions->row->status === 'pending') {
                    $buttons[] = AdminButtonHelper::apiButton([
                        'text'          => __t('Cancel'),
                        'icon'          => 'fa fa-times',
                        'class'         => 'text-danger',
                        'function_name' => 'cancelNotification',
                        'api_url'       => admin_route('api.notifications.cancel', ['id' => '__ID__']),
                        'payload'       => $id,
                    ]);
                }

                if (! empty($buttons)) {
                    $actions->append(AdminButtonHelper::buttonGroup($buttons));
                }
            });

            $grid->tools(function (Tools $tools) {
                $tools->append($this->processQueueButton());
            });
        });
    }

    protected function statisticsGrid(): string
    {
        admin_script(AdminButtonHelper::scriptForApiButton([
            'function_name'   => 'processQueue',
            'api_url'         => admin_route('api.notifications.process-queue'),
            'success_message' => __t('Queue processing started'),
        ]));
        admin_script(AdminButtonHelper::scriptForApiButton([
            'function_name'   => 'retryFailed',
            'api_url'         => admin_route('api.notifications.retry-failed'),
            'confirm'         => __t('Are you sure you want to retry all failed notifications?'),
            'success_message' => __t('Failed notifications queued for retry'),
        ]));
        admin_script(AdminButtonHelper::scriptForApiButton([
            'function_name'   => 'cleanOldSent',
            'api_url'         => admin_route('api.notifications.clean-old'),
            'method'          => 'DELETE',
            'confirm'         => __t('Are you sure you want to delete old sent notifications?'),
            'success_message' => __t('Old sent notifications cleaned'),
        ]));

        $stats      = $this->notificationService->getStatistics();
        $queueStats = $this->queueRepository->getStatistics();

        $html = '<div class="row">';

        // Templates Statistics
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card text-white bg-primary">';
        $html .= '<div class="card-header">' . __t('Templates') . '</div>';
        $html .= '<div class="card-body">';
        $html .= '<h2 class="card-title">' . $stats['templates_count'] . '</h2>';
        $html .= '<p class="card-text">' . __t('Active Templates') . '</p>';
        $html .= '</div></div></div>';

        // Rules Statistics
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card text-white bg-info">';
        $html .= '<div class="card-header">' . __t('Rules') . '</div>';
        $html .= '<div class="card-body">';
        $html .= '<h2 class="card-title">' . $stats['rules_count'] . '</h2>';
        $html .= '<p class="card-text">' . __t('Active Rules') . '</p>';
        $html .= '</div></div></div>';

        // Pending Queue
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card text-white bg-warning">';
        $html .= '<div class="card-header">' . __t('Pending') . '</div>';
        $html .= '<div class="card-body">';
        $html .= '<h2 class="card-title">' . $queueStats['pending'] . '</h2>';
        $html .= '<p class="card-text">' . __t('Pending Notifications') . '</p>';
        $html .= '</div></div></div>';

        // Sent Today
        $html .= '<div class="col-md-3">';
        $html .= '<div class="card text-white bg-success">';
        $html .= '<div class="card-header">' . __t('Sent Today') . '</div>';
        $html .= '<div class="card-body">';
        $html .= '<h2 class="card-title">' . $queueStats['today'] . '</h2>';
        $html .= '<p class="card-text">' . __t('Notifications Sent') . '</p>';
        $html .= '</div></div></div>';

        $html .= '</div>';

        // Additional Statistics
        $html .= '<div class="row mt-4">';
        $html .= '<div class="col-md-6">';
        $html .= '<div class="card">';
        $html .= '<div class="card-header">' . __t('Queue Statistics') . '</div>';
        $html .= '<div class="card-body">';
        $html .= '<table class="table table-borderless">';
        $html .= '<tr><td>' . __t('Total') . ':</td><td><span class="badge bg-primary">' . $queueStats['total'] . '</span></td></tr>';
        $html .= '<tr><td>' . __t('Sent') . ':</td><td><span class="badge bg-success">' . $queueStats['sent'] . '</span></td></tr>';
        $html .= '<tr><td>' . __t('Failed') . ':</td><td><span class="badge bg-danger">' . $queueStats['failed'] . '</span></td></tr>';
        $html .= '<tr><td>' . __t('This Week') . ':</td><td><span class="badge bg-info">' . $queueStats['this_week'] . '</span></td></tr>';
        $html .= '<tr><td>' . __t('This Month') . ':</td><td><span class="badge bg-secondary">' . $queueStats['this_month'] . '</span></td></tr>';
        $html .= '</table>';
        $html .= '</div></div></div>';

        $html .= '<div class="col-md-6">';
        $html .= '<div class="card">';
        $html .= '<div class="card-header">' . __t('Actions') . '</div>';
        $html .= '<div class="card-body">';
        $html .= AdminButtonHelper::customButton(
            __t('Process Queue'),
            'processQueue',
            admin_route('api.notifications.process-queue'),
            [
                'icon'  => 'fa fa-cog',
                'class' => 'btn btn-primary btn-block mb-2',
            ]
        );
        $html .= $this->retryFailedButton();
        $html .= $this->cleanOldSentButton();
        $html .= '</div></div></div>';

        $html .= '</div>';

        return $html;
    }

    // Notification-specific button helpers

    /**
     * Generate retry button for notifications
     */
    protected function retryButton(int $id): string
    {
        return AdminButtonHelper::apiButton([
            'text'          => __t('Retry'),
            'icon'          => 'fa fa-refresh',
            'class'         => 'text-warning',
            'function_name' => 'retryNotification',
            'api_url'       => admin_route('api.notifications.retry', ['id' => $id]),
            'payload'       => $id,
        ]);
    }

    /**
     * Generate cancel button for notifications
     */
    protected function cancelButton(int $id): string
    {
        return AdminButtonHelper::apiButton([
            'text'          => __t('Cancel'),
            'icon'          => 'fa fa-times',
            'class'         => 'text-danger',
            'function_name' => 'cancelNotification',
            'api_url'       => admin_route('api.notifications.cancel', ['id' => $id]),
            'payload'       => $id,
        ]);
    }

    /**
     * Generate test button for notification rules
     */
    public static function testRuleButton(int $id): string
    {
        return AdminButtonHelper::apiButton([
            'text'          => __t('Test'),
            'icon'          => 'fa fa-flask',
            'class'         => '',
            'function_name' => 'testNotificationRule',
            'api_url'       => admin_route('api.notifications.test-rule', ['id' => $id]),
            'payload'       => $id,
        ]);
    }

    /**
     * Generate duplicate template button
     */
    protected function duplicateTemplateButton(int $id): string
    {
        return AdminButtonHelper::duplicateButton($id, admin_route('api.notifications.duplicate-template', ['id' => $id]));
    }

    /**
     * Generate process queue button
     */
    protected function processQueueButton(): string
    {
        return AdminButtonHelper::customButton(
            __t('Process Queue'),
            'processQueue',
            admin_route('api.notifications.process-queue'),
            [
                'icon'  => 'fa fa-cog',
                'class' => 'btn btn-sm btn-success',
            ]
        );
    }

    /**
     * Generate retry failed button
     */
    protected function retryFailedButton(): string
    {
        return AdminButtonHelper::customButton(
            __t('Retry Failed'),
            'retryFailed',
            admin_route('api.notifications.retry-failed'),
            [
                'icon'  => 'fa fa-refresh',
                'class' => 'btn btn-warning btn-block mb-2',
            ]
        );
    }

    /**
     * Generate clean old sent button
     */
    protected function cleanOldSentButton(): string
    {
        return AdminButtonHelper::customButton(
            __t('Clean Old Sent'),
            'cleanOldSent',
            admin_route('api.notifications.clean-old'),
            [
                'icon'  => 'fa fa-trash-o',
                'class' => 'btn btn-info btn-block mb-2',
            ]
        );
    }
}
