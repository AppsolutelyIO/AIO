<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Form Submission Notification Template for Staff
        NotificationTemplate::updateOrCreate(
            ['slug' => 'form-submission-staff-notification'],
            [
                'name'      => 'Form Submission - Staff Notification',
                'category'  => 'form',
                'subject'   => 'New {{form_name}} Submission from {{user_name}}',
                'body_html' => $this->getHtmlTemplate(),
                'body_text' => $this->getTextTemplate(),
                'variables' => [
                    'form_name',
                    'user_name',
                    'form_fields_html',
                    'form_fields_text',
                ],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ]
        );
    }

    /**
     * Get HTML email template
     */
    private function getHtmlTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .header-section h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .header-section p {
            margin: 0;
            color: #666;
        }
        .content-section {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .content-section h3 {
            color: #2c3e50;
            margin-top: 0;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        #form-fields-table {
            width: 100%;
            border-collapse: collapse;
        }
        .form-field-label {
            padding: 8px 0;
            font-weight: bold;
            width: 150px;
        }
        .form-field-value {
            padding: 8px 0;
        }
        .footer-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h2>New Form Submission Received</h2>
        <p>A new form submission has been received and requires your attention.</p>
    </div>

    <div class="content-section">
        <h3>Form Submission Details</h3>
        {{form_fields_html}}
    </div>

    <div class="footer-section">
        <p>This is an automated notification. Please do not reply to this email.</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get plain text email template
     */
    private function getTextTemplate(): string
    {
        return <<<'TEXT'
NEW FORM SUBMISSION RECEIVED
============================

A new form submission has been received and requires your attention.

FORM: {{form_name}}

SUBMISSION DETAILS
-------------------
{{form_fields_text}}

---
This is an automated notification. Please do not reply to this email.
TEXT;
    }
}
