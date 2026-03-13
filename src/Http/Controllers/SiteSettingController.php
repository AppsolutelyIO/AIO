<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Form;
use Appsolutely\AIO\Layout\Content;
use Appsolutely\AIO\Models\SiteSetting;

class SiteSettingController extends AdminController
{
    protected $title = 'Site Settings';

    /**
     * Settings form page — grouped by tabs.
     */
    public function index(Content $content): Content
    {
        return $content
            ->title($this->title())
            ->description(trans('admin.edit'))
            ->body($this->settingsForm());
    }

    /**
     * Save settings.
     */
    public function store()
    {
        $input = request()->except('_token', '_method');

        foreach ($input as $key => $value) {
            $realKey = str_replace('-', '.', $key);

            SiteSetting::query()
                ->where('key', $realKey)
                ->update(['value' => is_array($value) ? json_encode($value) : (string) $value]);
        }

        admin_toastr(trans('admin.save_succeeded'));

        return redirect()->back();
    }

    /**
     * Build the tabbed settings form.
     */
    protected function settingsForm(): Form
    {
        return Form::make(new SiteSetting(), function (Form $form) {
            $form->disableDeleteButton();
            $form->disableViewButton();
            $form->disableCreatingCheck();
            $form->disableListButton();
            $form->disableEditingCheck();
            $form->disableViewCheck();

            $form->action(admin_url('site-settings'));

            $settings = SiteSetting::query()
                ->orderBy('group')
                ->orderBy('id')
                ->get();

            $grouped = $settings->groupBy('group');

            foreach ($grouped as $group => $items) {
                $label = ucfirst($group);

                $form->tab($label, function () use ($form, $items) {
                    foreach ($items as $setting) {
                        $fieldName = str_replace('.', '-', $setting->key);
                        $fieldLabel = $this->keyToLabel($setting->key);

                        $field = match ($setting->type) {
                            'text'    => $form->textarea($fieldName, $fieldLabel),
                            'boolean' => $form->switch($fieldName, $fieldLabel),
                            'integer' => $form->number($fieldName, $fieldLabel),
                            'image'   => $form->image($fieldName, $fieldLabel),
                            'json'    => $form->textarea($fieldName, $fieldLabel)->rows(5),
                            default   => $form->text($fieldName, $fieldLabel),
                        };

                        $field->value($setting->value);
                    }
                });
            }
        });
    }

    /**
     * Convert a dot-notation key to a human-readable label.
     *
     * e.g. 'general.site_name' → 'Site Name'
     */
    protected function keyToLabel(string $key): string
    {
        $parts = explode('.', $key);
        $last = end($parts);

        return ucwords(str_replace('_', ' ', $last));
    }
}
