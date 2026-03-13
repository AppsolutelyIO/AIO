<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;

final class NotificationRuleForm extends ModelForm
{
    protected NotificationRuleRepository $repository;

    protected NotificationTemplateRepository $templateRepository;

    protected NotificationSenderRepository $senderRepository;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->repository         = app(NotificationRuleRepository::class);
        $this->templateRepository = app(NotificationTemplateRepository::class);
        $this->senderRepository   = app(NotificationSenderRepository::class);
    }

    protected function initializeModel(): void
    {
        $this->model = new NotificationRule();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('name', __t('Rule Name'))->required()
            ->help(__t('Enter a descriptive name for this notification rule'));

        $this->select('template_id', __t('Email Template'))->options(
            $this->templateRepository->getActive()->pluck('name', 'id')->toArray()
        )->required()
            ->help(__t('Select which email template to use for this rule'));

        $this->select('sender_id', __t('Email Sender'))->options(
            $this->senderRepository->getActive()->pluck('name', 'id')->toArray()
        )->help(__t('Optional: Select specific sender. If not selected, will auto-detect based on recipient type'));

        $this->divider();

        $this->html('<h5>' . __t('Trigger Settings') . '</h5>');

        $this->select('trigger_type', __t('Trigger Type'))->options([
            'form_submission'   => __t('Form Submission'),
            'user_registration' => __t('User Registration'),
            'user_login'        => __t('User Login'),
            'order_created'     => __t('Order Created'),
            'order_completed'   => __t('Order Completed'),
            'payment_received'  => __t('Payment Received'),
            'custom_event'      => __t('Custom Event'),
        ])->required()
            ->help(__t('When should this notification be triggered?'));

        $this->text('trigger_reference', __t('Trigger Reference'))
            ->help(__t('Optional: Specific reference like form slug, order status, etc. Leave empty to match all (use * as wildcard)'));

        $this->divider();

        $this->html('<h5>' . __t('Recipient Settings') . '</h5>');

        $this->select('recipient_type', __t('Recipient Type'))->options([
            'admin'       => __t('Admin Users'),
            'user'        => __t('Triggering User'),
            'custom'      => __t('Custom Email'),
            'conditional' => __t('Conditional (Based on Data)'),
        ])->required()
            ->help(__t('Who should receive this notification?'));

        // Custom emails field - only show when recipient_type is 'custom'
        $this->tags('recipient_emails', __t('Custom Emails'))
            ->help(__t('Required if recipient type is Custom. Enter email addresses'));

        // Conditions field - only show when recipient_type is 'conditional'
        $this->textarea('conditions', __t('Conditions'))->rows(5)
            ->help(__t('JSON conditions. Example: {"field": "email_domain", "operator": "equals", "value": "company.com"}'));

        $this->divider();

        $this->html('<h5>' . __t('Timing') . '</h5>');

        $this->number('delay_minutes', __t('Delay (Minutes)'))->default(0)
            ->help(__t('Delay before sending notification (0 = immediate)'));

        $this->divider();

        $this->html('<h5>' . __t('Settings') . '</h5>');

        $this->switch('status', __t('Status'))
            ->help(__t('Enable or disable this rule'))
            ->default(true);
    }

    public function handle(array $input)
    {
        // Set default trigger_reference if empty
        // Use '*' as wildcard to match all forms/events of the trigger type
        if (empty($input['trigger_reference'] ?? '')) {
            $input['trigger_reference'] = '*';
        }

        // Ensure recipient_emails is an array
        if (isset($input['recipient_emails']) && is_string($input['recipient_emails'])) {
            // If it's a string (from tags field), convert to array
            $input['recipient_emails'] = array_filter(
                array_map('trim', explode(',', $input['recipient_emails']))
            );
        }

        // Ensure conditions is valid JSON if provided
        if (isset($input['conditions']) && ! empty($input['conditions'])) {
            if (is_string($input['conditions'])) {
                $decoded = json_decode($input['conditions'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $input['conditions'] = $decoded;
                } else {
                    return $this->response()->error(__t('Invalid JSON in conditions: ') . json_last_error_msg());
                }
            }
        }

        return parent::handle($input);
    }
}
