<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\NotificationTemplate;

final class NotificationTemplateForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new NotificationTemplate();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('name', __t('Template Name'))->required()
            ->help(__t('Enter a unique name for this email template'));

        $this->text('slug', __t('Slug'))
            ->help(__t('URL-friendly identifier. Leave empty to auto-generate from name'));

        $this->select('category', __t('Category'))->options([
            'form'      => __t('Form Submissions'),
            'user'      => __t('User Account'),
            'order'     => __t('Order Management'),
            'system'    => __t('System Notifications'),
            'marketing' => __t('Marketing'),
            'custom'    => __t('Custom'),
        ])->required()
            ->help(__t('Categorize this template for better organization'));

        $this->text('subject', __t('Email Subject'))->required()
            ->help(__t('Email subject line. You can use variables like {{user_name}}'));

        $this->divider();

        $this->html('<h5>' . __t('Email Content') . '</h5>');

        $this->textarea('body_html', __t('HTML Body'))->rows(15)->required()
            ->help(__t('HTML email content. Use variables like {{variable_name}} for dynamic content'));

        $this->textarea('body_text', __t('Text Body'))->rows(10)
            ->help(__t('Plain text version of the email (optional but recommended)'));

        $this->divider();

        $this->html('<h5>' . __t('Template Settings') . '</h5>');

        $this->text('from_email', __t('From Email'))
            ->help(__t('Override default from email for this template'));

        $this->text('from_name', __t('From Name'))
            ->help(__t('Override default from name for this template'));

        $this->text('reply_to', __t('Reply To'))
            ->help(__t('Reply-to email address'));

        $this->tags('variables', __t('Available Variables'))
            ->help(__t('List of variables that can be used in this template, e.g., user_name, form_data, order_id'));

        $this->textarea('description', __t('Description'))->rows(3)
            ->help(__t('Internal description of when and how this template is used'));

        $this->switch('is_system', __t('System Template'))
            ->help(__t('System templates cannot be deleted and are used by the application'))
            ->default(false);

        $this->switch('status', __t('Status'))
            ->help(__t('Enable or disable this template'))
            ->default(true);
    }
}
