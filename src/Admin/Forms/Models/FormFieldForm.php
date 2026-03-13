<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Repositories\FormFieldRepository;
use Appsolutely\AIO\Repositories\FormRepository;

final class FormFieldForm extends ModelForm
{
    protected FormFieldRepository $repository;

    protected FormRepository $formRepository;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->repository     = app(FormFieldRepository::class);
        $this->formRepository = app(FormRepository::class);
    }

    protected function initializeModel(): void
    {
        $this->model = new FormField();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->select('form_id', __t('Form'))->options(
            $this->formRepository->model->newQuery()
                ->where('status', Status::ACTIVE)
                ->pluck('name', 'id')
                ->toArray()
        )->required()
            ->help(__t('Select which form this field belongs to'));

        $this->text('label', __t('Field Label'))->required()
            ->help(__t('The label shown to users'));

        $this->text('name', __t('Field Name'))->required()
            ->help(__t('Internal field name (used in form submission). Use lowercase with underscores, e.g., "full_name"'));

        $this->select('type', __t('Field Type'))->options(FormFieldType::toArray())->required()
            ->help(__t('Choose the type of input field'));

        $this->text('placeholder', __t('Placeholder'))
            ->help(__t('Optional placeholder text shown in the input field'));

        $this->switch('required', __t('Required'))
            ->help(__t('Whether this field must be filled'));

        $this->tags('options', __t('Field Options'))
            ->help(__t('For select, radio, and checkbox fields. Enter each option and press Enter'))
            ->when('type', ['select', 'multiple_select', 'radio', 'checkbox'], function ($form) {
                $form->show();
            })
            ->when('type', ['text', 'textarea', 'email', 'number', 'date', 'time', 'datetime', 'file', 'hidden'], function ($form) {
                $form->hide();
            });

        $this->number('sort', __t('Sort Order'))->default(0)
            ->help(__t('Display order of this field (lower numbers appear first)'));

        $this->divider();

        $this->html('<h5>' . __t('Advanced Settings') . '</h5>');

        $this->number('setting[max]', __t('Maximum Length/Value'))
            ->help(__t('For text fields: max characters. For number fields: max value'));

        $this->number('setting[min]', __t('Minimum Length/Value'))
            ->help(__t('For text fields: min characters. For number fields: min value'));

        $this->text('setting[pattern]', __t('Validation Pattern'))
            ->help(__t('Regular expression pattern for validation'));

        $this->text('setting[default]', __t('Default Value'))
            ->help(__t('Default value for this field'));

        $this->switch('setting[readonly]', __t('Read Only'))
            ->help(__t('Make this field read-only'));

        $this->number('setting[rows]', __t('Textarea Rows'))->default(4)
            ->help(__t('Number of rows for textarea fields'))
            ->when('type', 'textarea', function ($form) {
                $form->show();
            })
            ->when('type', ['text', 'email', 'number', 'select', 'multiple_select', 'radio', 'checkbox', 'date', 'time', 'datetime', 'file', 'hidden'], function ($form) {
                $form->hide();
            });

        $this->tags('setting[mimes]', __t('Allowed File Types'))
            ->help(__t('Allowed file extensions for file uploads, e.g., jpg, png, pdf'))
            ->when('type', 'file', function ($form) {
                $form->show();
            })
            ->when('type', ['text', 'textarea', 'email', 'number', 'select', 'multiple_select', 'radio', 'checkbox', 'date', 'time', 'datetime', 'hidden'], function ($form) {
                $form->hide();
            });

        $this->number('setting[max_size]', __t('Max File Size (KB)'))
            ->help(__t('Maximum file size in kilobytes'))
            ->when('type', 'file', function ($form) {
                $form->show();
            })
            ->when('type', ['text', 'textarea', 'email', 'number', 'select', 'multiple_select', 'radio', 'checkbox', 'date', 'time', 'datetime', 'hidden'], function ($form) {
                $form->hide();
            });

        $this->textarea('setting[validation]', __t('Custom Validation Rules'))->rows(2)
            ->help(__t('Laravel validation rules, e.g., "required|email|max:255"'));
    }
}
