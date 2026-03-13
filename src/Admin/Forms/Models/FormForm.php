<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\Form;

final class FormForm extends ModelForm
{
    public function __construct(?int $id = null)
    {
        parent::__construct($id);
    }

    protected function initializeModel(): void
    {
        $this->model = new Form();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('name', __t('Form Name'))->required()
            ->help(__t('Enter a descriptive name for the form'));

        $this->text('slug', __t('Slug'))
            ->help(__t('URL-friendly identifier. Leave empty to auto-generate from name'));

        $this->textarea('description', __t('Description'))->rows(3)
            ->help(__t('Optional description that will be shown to users'));

        $this->text('target_table', __t('Target Table'))
            ->help(__t('Optional: Database table name to also store submissions (e.g., test_drive_bookings)'));

        $this->text('api_access_token', __t('API access token'))
            ->help(__t('Optional: Token for pulling entries via API (GET /api/forms/entries?form_slug=...). Send as Bearer token or query param "token". Leave empty to disable API access for this form.'));

        $this->text('meta_keys_to_collect', __t('Meta keys to collect'))
            ->placeholder('page_url, utm_source, utm_medium, utm_campaign, utm_term, utm_content, gclid, fbclid, referrer, first_visit_at')
            ->help(__t('Meta keys to collect from cookies when form is submitted. Each key must match a cookie name. Comma-separated list. Example: page_url, utm_source, utm_medium, utm_campaign, utm_term, utm_content, gclid, fbclid, referrer, first_visit_at'));

        $this->switch('status', __t('Status'))
            ->help(__t('Enable or disable this form'));
    }

    protected function fillModelData(int $id): void
    {
        $model = $this->model->with($this->getRelationships())->find($id);

        if ($model) {
            $data = $model->toArray();
            if (isset($data['meta_keys_to_collect']) && is_array($data['meta_keys_to_collect'])) {
                $data['meta_keys_to_collect'] = implode(', ', $data['meta_keys_to_collect']);
            }
            $this->fill($data);
        }
    }

    public function handle(array $input)
    {
        if (isset($input['meta_keys_to_collect'])) {
            $value                         = $input['meta_keys_to_collect'];
            $input['meta_keys_to_collect'] = is_array($value)
                ? array_values(array_filter(array_map('trim', $value)))
                : array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY))));
        }

        return parent::handle($input);
    }
}
