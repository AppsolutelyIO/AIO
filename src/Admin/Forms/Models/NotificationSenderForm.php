<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Illuminate\Support\Str;

final class NotificationSenderForm extends ModelForm
{
    protected NotificationSenderRepository $repository;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->repository = app(NotificationSenderRepository::class);
    }

    protected function initializeModel(): void
    {
        $this->model = new NotificationSender();
    }

    /**
     * Create a new model using repository
     */
    protected function createModel(array $input): void
    {
        $relationships = $this->extractRelationships($input);
        $modelData     = $this->extractModelData($input);

        $this->repository->create($modelData);

        // Note: Relationships are not used for NotificationSender, but kept for consistency
        $this->syncRelationships($relationships);
    }

    /**
     * Update existing model using repository
     */
    protected function updateModel(int $id, array $input): void
    {
        $relationships = $this->extractRelationships($input);
        $modelData     = $this->extractModelData($input);

        $this->repository->update($modelData, $id);

        // Note: Relationships are not used for NotificationSender, but kept for consistency
        $this->syncRelationships($relationships, $this->repository->find($id));
    }

    /**
     * Fill form with model data, including populating service_config_json
     * Override to use repository instead of direct model access
     */
    protected function fillModelData(int $id): void
    {
        // Use repository to find the model
        $model = $this->repository->find($id);

        if ($model) {
            $this->fill($model->toArray());
        }

        // Populate service_config_json from decrypted service_config when editing
        try {
            if ($model && ! empty($model->service_config)) {
                try {
                    $decrypted = $model->decrypted_service_config;
                    if ($decrypted && is_array($decrypted)) {
                        $this->fill(['service_config_json' => json_encode($decrypted, JSON_PRETTY_PRINT)]);
                    }
                } catch (\Exception $e) {
                    // If decryption fails, just skip populating service_config_json
                    // User will need to re-enter the configuration
                }
            }
        } catch (\Exception $e) {
            // Silently fail - form will still work without pre-populated service config
        }
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $this->text('name', __t('Sender Name'))->required()
            ->help(__t('Display name for this sender configuration (e.g., "Internal Staff Mailer", "Customer Transactional")'));

        $this->text('slug', __t('Slug'))->required()
            ->help(__t('Unique identifier (e.g., internal-default, customer-transactional). Leave empty to auto-generate from name'));

        $this->select('category', __t('Category'))->options([
            'internal' => __t('Internal (Staff/Admin)'),
            'external' => __t('External (Customers/Public)'),
            'system'   => __t('System Alerts'),
        ])->required()
            ->help(__t('Category determines when this sender is used automatically'));

        $this->select('type', __t('Mailer Type'))->options([
            'smtp'     => 'SMTP',
            'sendmail' => 'Sendmail',
            'mailgun'  => 'Mailgun',
            'ses'      => 'Amazon SES',
            'postmark' => 'Postmark',
            'resend'   => 'Resend',
            'log'      => 'Log (Testing)',
        ])->required()
            ->default('smtp')
            ->help(__t('Select the email transport method'));

        $this->divider();

        $this->html('<h5>' . __t('SMTP Configuration') . '</h5>');
        $this->html('<p class="text-muted">' . __t('Configure these fields when Mailer Type is SMTP') . '</p>');

        $this->text('smtp_host', __t('SMTP Host'))
            ->help(__t('SMTP server hostname (e.g., smtp.gmail.com). Required when type is SMTP'));

        $this->number('smtp_port', __t('SMTP Port'))->default(587)
            ->help(__t('SMTP server port (usually 587 for TLS, 465 for SSL)'));

        $this->text('smtp_username', __t('SMTP Username'))
            ->help(__t('SMTP authentication username'));

        $this->password('smtp_password', __t('SMTP Password'))
            ->help(__t('SMTP authentication password (will be encrypted)'));

        $this->select('smtp_encryption', __t('Encryption'))
            ->options(['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None'])
            ->help(__t('SMTP encryption type'));

        $this->divider();

        $this->html('<h5>' . __t('Third-Party Service Configuration') . '</h5>');
        $this->html('<p class="text-muted">' . __t('Configure this when using Mailgun, SES, Postmark, or Resend') . '</p>');

        $this->textarea('service_config_json', __t('Service Configuration (JSON)'))
            ->rows(5)
            ->help(__t('Enter service credentials as JSON. Examples:') . '<br>' .
                'Mailgun: {"domain": "your-domain.com", "secret": "your-secret-key"}' . '<br>' .
                'SES: {"key": "access-key", "secret": "secret-key", "region": "us-east-1"}' . '<br>' .
                'Postmark: {"token": "your-token"}' . '<br>' .
                'Resend: {"key": "your-api-key"}');

        $this->divider();

        $this->html('<h5>' . __t('From Address') . '</h5>');

        $this->email('from_address', __t('From Email Address'))->required()
            ->help(__t('Email address that will appear as the sender'));

        $this->text('from_name', __t('From Name'))
            ->help(__t('Display name for the sender (e.g., "Company Name")'));

        $this->divider();

        $this->html('<h5>' . __t('Settings') . '</h5>');

        $this->switch('is_default', __t('Default for Category'))
            ->help(__t('Use as default sender for this category when no sender is specified'));

        $this->number('priority', __t('Priority'))->default(0)
            ->help(__t('Higher priority senders are preferred when multiple senders exist for the same category'));

        $this->switch('is_active', __t('Active'))->default(true)
            ->help(__t('Only active senders can be used'));

        $this->divider();

        $this->html('<h5>' . __t('Rate Limiting (Optional)') . '</h5>');

        $this->number('daily_limit', __t('Daily Limit'))
            ->help(__t('Maximum emails per day (leave empty for unlimited)'));

        $this->number('hourly_limit', __t('Hourly Limit'))
            ->help(__t('Maximum emails per hour (leave empty for unlimited)'));

        $this->divider();

        $this->textarea('description', __t('Description'))->rows(3)
            ->help(__t('Optional description for this sender configuration'));
    }

    public function handle(array $input)
    {
        // Validate SMTP fields when type is smtp
        if (($input['type'] ?? '') === 'smtp') {
            if (empty($input['smtp_host'] ?? '')) {
                return $this->response()->error(__t('SMTP Host is required when mailer type is SMTP'));
            }
        }

        // Handle service_config JSON encoding
        if (isset($input['service_config_json'])) {
            $json = $input['service_config_json'];
            if (! empty($json)) {
                $decoded = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $input['service_config'] = $decoded;
                } else {
                    return $this->response()->error(__t('Invalid JSON in service configuration: ') . json_last_error_msg());
                }
            }
            unset($input['service_config_json']);
        }

        // Auto-generate slug if not provided
        if (empty($input['slug']) && ! empty($input['name'])) {
            $input['slug'] = Str::slug($input['name']);
        }

        return parent::handle($input);
    }
}
