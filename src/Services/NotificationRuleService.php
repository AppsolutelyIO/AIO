<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Exceptions\NotFoundException;
use Appsolutely\AIO\Models\Model;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Services\Contracts\NotificationRuleServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class NotificationRuleService implements NotificationRuleServiceInterface
{
    public function __construct(
        private NotificationRuleRepository $ruleRepository
    ) {}

    public function findRulesForTrigger(string $triggerType, string $reference): Collection
    {
        return $this->ruleRepository->findByTrigger($triggerType, $reference);
    }

    public function evaluateConditions(NotificationRule $rule, array $data): bool
    {
        return $rule->evaluateConditions($data);
    }

    public function getRecipients(NotificationRule $rule, array $data): array
    {
        return match ($rule->recipient_type) {
            'admin'       => $this->getAdminEmails(),
            'user'        => $this->getUserEmails($data),
            'custom'      => $rule->recipient_emails_list,
            'conditional' => $this->getConditionalEmails($rule, $data),
            default       => []
        };
    }

    public function createRule(array $data): Model
    {
        return $this->ruleRepository->createRule($data);
    }

    public function updateRule(NotificationRule $rule, array $data): NotificationRule
    {
        return $this->ruleRepository->updateRule($rule->id, $data);
    }

    public function testRule(NotificationRule $rule, array $sampleData): array
    {
        $result = [
            'conditions_met'   => $this->evaluateConditions($rule, $sampleData),
            'recipients'       => [],
            'template_preview' => null,
        ];

        if ($result['conditions_met']) {
            $result['recipients']       = $this->getRecipients($rule, $sampleData);
            $result['template_preview'] = $rule->template->render($sampleData);
        }

        return $result;
    }

    public function testRuleById(int $id): array
    {
        $rule = $this->ruleRepository->find($id);

        if (! $rule) {
            throw new NotFoundException(
                (string) $id,
                'The notification rule could not be found.'
            );
        }

        $sampleData = $this->generateSampleData($rule->trigger_type);

        return $this->testRule($rule, $sampleData);
    }

    protected function generateSampleData(string $triggerType): array
    {
        return match ($triggerType) {
            'form_submission' => [
                'name'         => 'John Doe',
                'email'        => 'john@example.com',
                'phone'        => '+1234567890',
                'message'      => 'This is a test message',
                'form_name'    => 'Contact Form',
                'submitted_at' => now()->toDateTimeString(),
            ],
            'user_registration' => [
                'name'          => 'Jane Smith',
                'email'         => 'jane@example.com',
                'registered_at' => now()->toDateTimeString(),
                'ip_address'    => '192.168.1.1',
            ],
            'order_placed' => [
                'order_id'       => 'ORD-12345',
                'customer_name'  => 'John Customer',
                'customer_email' => 'customer@example.com',
                'total_amount'   => 99.99,
                'order_date'     => now()->toDateTimeString(),
            ],
            default => [
                'event_type' => $triggerType,
                'timestamp'  => now()->toDateTimeString(),
                'data'       => 'Sample data for ' . $triggerType,
            ]
        };
    }

    public function getAvailableTriggerTypes(): array
    {
        return [
            'form_submission'        => 'Form Submission',
            'user_registration'      => 'User Registration',
            'user_login'             => 'User Login',
            'order_placed'           => 'Order Placed',
            'order_shipped'          => 'Order Shipped',
            'order_delivered'        => 'Order Delivered',
            'payment_received'       => 'Payment Received',
            'subscription_created'   => 'Subscription Created',
            'subscription_cancelled' => 'Subscription Cancelled',
            'system_error'           => 'System Error',
            'custom'                 => 'Custom Event',
        ];
    }

    public function getConditionOperators(): array
    {
        return [
            'equals'       => 'Equals',
            'not_equals'   => 'Not Equals',
            'contains'     => 'Contains',
            'starts_with'  => 'Starts With',
            'ends_with'    => 'Ends With',
            'in'           => 'In List',
            'greater_than' => 'Greater Than',
            'less_than'    => 'Less Than',
        ];
    }

    public function getRecipientTypes(): array
    {
        return [
            'admin'       => 'Admin Emails',
            'user'        => 'Form Submitter',
            'custom'      => 'Custom Email List',
            'conditional' => 'Conditional Recipients',
        ];
    }

    protected function getAdminEmails(): array
    {
        $adminEmails = config('notifications.admin_emails', []);

        if (empty($adminEmails)) {
            $adminEmails = [config('mail.from.address', 'admin@example.com')];
        }

        return $adminEmails;
    }

    protected function getUserEmails(array $data): array
    {
        $emails = [];

        $emailFields = config('notifications.email_field_names', ['email', 'user_email', 'customer_email', 'contact_email']);

        foreach ($emailFields as $field) {
            if (isset($data[$field]) && filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $emails[] = $data[$field];
            }
        }

        return array_unique($emails);
    }

    protected function getConditionalEmails(NotificationRule $rule, array $data): array
    {
        if ($rule->evaluateConditions($data)) {
            return $rule->recipient_emails_list;
        }

        return [];
    }

    public function validateRule(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Rule name is required';
        }

        if (empty($data['trigger_type'])) {
            $errors[] = 'Trigger type is required';
        }

        if (empty($data['template_id'])) {
            $errors[] = 'Template is required';
        }

        if ($data['recipient_type'] === 'custom' && empty($data['recipient_emails'])) {
            $errors[] = 'Recipient emails are required for custom type';
        }

        if (! empty($data['conditions'])) {
            $conditions = $data['conditions'];
            if (empty($conditions['field']) || empty($conditions['operator'])) {
                $errors[] = 'Complete condition configuration is required';
            }
        }

        return $errors;
    }
}
