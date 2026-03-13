<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Models\FormEntry;

final class FormEntryForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new FormEntry();
    }

    /**
     * Load entry with form and form.fields so Form name and formatted_data display correctly.
     */
    protected function fillModelData(int $id): void
    {
        $model = $this->model->with(['form', 'form.fields'])->find($id);

        if ($model) {
            $this->model       = $model;
            $data              = $model->toArray();
            $data['data_json'] = json_encode($model->data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $data['meta_json'] = json_encode($model->meta ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->fill($data);
        }
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        // Basic entry information
        $this->display('form.name', __t('Form'));
        $this->display('submitted_at', __t('Submitted At'));

        $this->divider();

        // Contact information (editable)
        $this->html('<h5>' . __t('Contact Information') . '</h5>');

        $this->text('name', __t('Name'));
        $this->text('first_name', __t('First Name'));
        $this->text('last_name', __t('Last Name'));
        $this->email('email', __t('Email'));
        $this->text('mobile', __t('Mobile'));
        $this->display('user.name', __t('User'))->default(__t('Guest'));

        $this->divider();

        // Form data (JSON view)
        $this->textarea('data_json', __t('Form Data'))->rows(8)->readonly()
            ->default('{}')
            ->help(__t('Form field values as JSON'));

        $this->divider();

        // Collected meta (JSON view)
        $this->textarea('meta_json', __t('Collected Meta'))->rows(6)->readonly()
            ->default('{}')
            ->help(__t('Meta from cookies as JSON'));

        $this->divider();

        // Meta information (editable where appropriate)
        $this->html('<h5>' . __t('Meta Information') . '</h5>');

        $this->switch('is_spam', __t('Spam?'))->default(FormEntrySpamStatus::Valid->value);

        $this->display('ip_address', __t('IP Address'));
        $this->text('referer', __t('Referer'));
        $this->textarea('user_agent', __t('User Agent'));
        $this->display('created_at', __t('Created At'));
        $this->display('updated_at', __t('Updated At'));
    }
}
