<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers;

use Appsolutely\AIO\Form;
use Appsolutely\AIO\Layout\Content;
use Appsolutely\AIO\Models\SiteSetting;
use Illuminate\Support\Collection;

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

                $form->tab($label, function () use ($form, $group, $items) {
                    $sections = $this->groupIntoSections($group, $items);

                    foreach ($sections as $sectionTitle => $sectionItems) {
                        if ($sectionTitle !== '') {
                            $form->fieldset($sectionTitle, function () use ($form, $sectionItems) {
                                $this->renderFields($form, $sectionItems);
                            });
                        } else {
                            $this->renderFields($form, $sectionItems);
                        }
                    }
                });
            }
        });
    }

    /**
     * Render a collection of settings as form fields.
     */
    protected function renderFields(Form $form, iterable $items): void
    {
        foreach ($items as $setting) {
            $fieldName  = str_replace('.', '-', $setting->key);
            $fieldLabel = $setting->label ?: $this->keyToLabel($setting->key);

            $field = match ($setting->type) {
                'text'    => $form->textarea($fieldName, $fieldLabel),
                'boolean' => $form->switch($fieldName, $fieldLabel),
                'integer' => $form->number($fieldName, $fieldLabel),
                'image'   => $form->image($fieldName, $fieldLabel),
                'json'    => $form->textarea($fieldName, $fieldLabel)->rows(5),
                default   => $form->text($fieldName, $fieldLabel),
            };

            $field->value($setting->value);

            if ($setting->description) {
                $field->help($setting->description);
            }
        }
    }

    /**
     * Group settings into sections by key structure.
     *
     * Keys with more than 2 segments (e.g. "forms.captcha.honeypot.enabled")
     * are grouped by the middle segments ("Captcha / Honeypot").
     * Keys with only 2 segments (e.g. "general.site_name") have no section.
     *
     * @return array<string, array>
     */
    protected function groupIntoSections(string $group, $items): array
    {
        $sections = [];

        foreach ($items as $setting) {
            $parts = explode('.', $setting->key);

            // Remove first segment (group) and last segment (field name)
            // e.g. "forms.captcha.honeypot.enabled" → ["captcha", "honeypot"]
            $middle = array_slice($parts, 1, -1);

            if (count($middle) > 0) {
                $sectionTitle = implode(' / ', array_map('ucfirst', $middle));
            } else {
                $sectionTitle = '';
            }

            $sections[$sectionTitle][] = $setting;
        }

        // If all sections share a common prefix, strip it
        $titles = array_keys(array_filter($sections, fn ($_, $k) => $k !== '', ARRAY_FILTER_USE_BOTH));
        if (count($titles) > 1) {
            $commonPrefix = $this->findCommonPrefix($titles);
            if ($commonPrefix !== '') {
                $renamed = [];
                foreach ($sections as $title => $sectionItems) {
                    $newTitle           = $title === '' ? '' : (ltrim(substr($title, strlen($commonPrefix)), ' /') ?: $title);
                    $renamed[$newTitle] = $sectionItems;
                }
                $sections = $renamed;
            }
        }

        return $sections;
    }

    /**
     * Find the common "X / Y / " prefix shared by all titles.
     */
    protected function findCommonPrefix(array $titles): string
    {
        if (count($titles) < 2) {
            return '';
        }

        $first = $titles[0];

        // Only strip at " / " boundaries
        $segments    = explode(' / ', $first);
        $commonCount = count($segments);

        foreach (array_slice($titles, 1) as $title) {
            $otherSegments = explode(' / ', $title);
            $max           = min($commonCount, count($otherSegments));
            $commonCount   = 0;

            for ($i = 0; $i < $max; $i++) {
                if ($segments[$i] === $otherSegments[$i]) {
                    $commonCount++;
                } else {
                    break;
                }
            }
        }

        if ($commonCount === 0) {
            return '';
        }

        return implode(' / ', array_slice($segments, 0, $commonCount)) . ' / ';
    }

    /**
     * Convert a dot-notation key to a human-readable label.
     *
     * e.g. 'general.site_name' → 'Site Name'
     */
    protected function keyToLabel(string $key): string
    {
        $parts = explode('.', $key);
        $last  = end($parts);

        return ucwords(str_replace('_', ' ', $last));
    }
}
