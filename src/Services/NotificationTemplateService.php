<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;
use Appsolutely\AIO\Services\Contracts\NotificationTemplateServiceInterface;

final readonly class NotificationTemplateService implements NotificationTemplateServiceInterface
{
    public function __construct(
        private readonly NotificationTemplateRepository $templateRepository
    ) {}

    public function getAvailableVariables(string $category): array
    {
        return match ($category) {
            'form' => [
                'form_name'        => 'Name of the form',
                'user_name'        => 'User\'s name (from name column or first_name + last_name)',
                'form_fields_html' => 'All form fields formatted as HTML table rows',
                'form_fields_text' => 'All form fields formatted as plain text',
            ],
            'user' => [
                'username'          => 'User\'s username',
                'email'             => 'User\'s email address',
                'full_name'         => 'User\'s full name',
                'registration_date' => 'Account registration date',
                'verification_link' => 'Email verification link',
                'profile_link'      => 'User profile link',
            ],
            'order' => [
                'order_number'     => 'Order number',
                'order_total'      => 'Total order amount',
                'customer_name'    => 'Customer\'s name',
                'customer_email'   => 'Customer\'s email',
                'order_date'       => 'Order placement date',
                'items_count'      => 'Number of items',
                'items_list'       => 'List of ordered items',
                'shipping_address' => 'Shipping address',
                'billing_address'  => 'Billing address',
            ],
            'system' => [
                'site_name'    => 'Website name',
                'site_url'     => 'Website URL',
                'admin_email'  => 'Admin email address',
                'current_date' => 'Current date',
                'current_time' => 'Current time',
            ],
            default => []
        };
    }

    public function createTemplate(array $data): NotificationTemplate
    {
        return $this->templateRepository->createWithUniqueSlug($data);
    }

    public function updateTemplate(NotificationTemplate $template, array $data): NotificationTemplate
    {
        return $this->templateRepository->updateWithSlug($template->id, $data);
    }

    public function renderPreview(NotificationTemplate $template, array $sampleData = []): array
    {
        $variables = $this->getSampleVariables($template->category);
        $variables = array_merge($variables, $sampleData);

        return $template->render($variables);
    }

    public function getSampleVariables(string $category): array
    {
        return match ($category) {
            'form' => [
                'form_name'        => 'Test Drive Booking Form',
                'user_name'        => 'John Doe',
                'form_fields_html' => '<tr><td class="form-field-label">Name:</td><td class="form-field-value">John Doe</td></tr>' . "\n" .
                    '<tr><td class="form-field-label">Email:</td><td class="form-field-value"><a href="mailto:john.doe@example.com" style="color: #3498db; text-decoration: none;">john.doe@example.com</a></td></tr>' . "\n" .
                    '<tr><td class="form-field-label">Phone:</td><td class="form-field-value">+1 234 567 8900</td></tr>' . "\n" .
                    '<tr><td class="form-field-label">Preferred Date:</td><td class="form-field-value">2024-01-15</td></tr>' . "\n" .
                    '<tr><td class="form-field-label">Vehicle Interest:</td><td class="form-field-value">Sedan Model A</td></tr>',
                'form_fields_text' => "\n" .
                    "Name: John Doe\n" .
                    "Email: john.doe@example.com\n" .
                    "Phone: +1 234 567 8900\n" .
                    "Preferred Date: 2024-01-15\n" .
                    'Vehicle Interest: Sedan Model A',
            ],
            'user' => [
                'username'          => 'johndoe',
                'email'             => 'john.doe@example.com',
                'full_name'         => 'John Doe',
                'registration_date' => today()->format('Y-m-d'),
                'verification_link' => url('/verify-email/sample-token'),
                'profile_link'      => url('/profile'),
            ],
            'order' => [
                'order_number'     => 'ORD-2024-001',
                'order_total'      => '$299.99',
                'customer_name'    => 'John Doe',
                'customer_email'   => 'john.doe@example.com',
                'order_date'       => today()->format('Y-m-d'),
                'items_count'      => '3',
                'items_list'       => 'Product A, Product B, Product C',
                'shipping_address' => '123 Main St, City, State 12345',
                'billing_address'  => '123 Main St, City, State 12345',
            ],
            'system' => [
                'site_name'    => config('app.name', 'Laravel'),
                'site_url'     => config('app.url'),
                'admin_email'  => config('mail.from.address'),
                'current_date' => today()->format('Y-m-d'),
                'current_time' => now()->format('H:i:s'),
            ],
            default => []
        };
    }

    public function validateTemplate(string $content, array $allowedVariables): array
    {
        $errors = [];

        preg_match_all('/\{(\w+)\}/', $content, $matches);
        $usedVariables = $matches[1] ?? [];

        foreach ($usedVariables as $variable) {
            if (! in_array($variable, array_keys($allowedVariables))) {
                $errors[] = "Undefined variable: {{$variable}}";
            }
        }

        return $errors;
    }

    public function duplicateTemplate(NotificationTemplate $template): NotificationTemplate
    {
        return $this->templateRepository->duplicate($template->id);
    }
}
