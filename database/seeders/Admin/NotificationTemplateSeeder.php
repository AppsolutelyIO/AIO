<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders\Admin;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->templates() as $template) {
            NotificationTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template,
            );
        }
    }

    // ─── Template Definitions ────────────────────────────────

    private function templates(): array
    {
        return [
            // ── Auth ─────────────────────────────────────────
            [
                'slug'      => 'welcome',
                'name'      => 'Welcome Email',
                'category'  => 'auth',
                'subject'   => 'Welcome to {{site_name}}, {{user_name}}!',
                'body_html' => $this->html('Welcome to {{site_name}}', '<p>Hi {{user_name}},</p><p>Thank you for creating an account. We\'re excited to have you on board.</p><p>If you have any questions, feel free to reach out to our support team.</p>'),
                'body_text' => $this->text("Welcome to {{site_name}}\n\nHi {{user_name}},\n\nThank you for creating an account. We're excited to have you on board.\n\nIf you have any questions, feel free to reach out to our support team."),
                'variables' => ['site_name', 'user_name', 'login_url'],
                'is_system' => true,
                'status'    => Status::ACTIVE,
            ],
            [
                'slug'      => 'email-verification',
                'name'      => 'Email Verification',
                'category'  => 'auth',
                'subject'   => 'Verify your email address',
                'body_html' => $this->html('Verify Your Email', '<p>Hi {{user_name}},</p><p>Please click the link below to verify your email address:</p><p style="text-align:center;margin:30px 0"><a href="{{verification_url}}" style="background-color:#3498db;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block">Verify Email</a></p><p>This link will expire in {{expiry_minutes}} minutes.</p><p>If you did not create an account, no further action is required.</p>'),
                'body_text' => $this->text("Verify Your Email\n\nHi {{user_name}},\n\nPlease visit the link below to verify your email address:\n\n{{verification_url}}\n\nThis link will expire in {{expiry_minutes}} minutes.\n\nIf you did not create an account, no further action is required."),
                'variables' => ['user_name', 'verification_url', 'expiry_minutes'],
                'is_system' => true,
                'status'    => Status::ACTIVE,
            ],
            [
                'slug'      => 'password-reset',
                'name'      => 'Password Reset',
                'category'  => 'auth',
                'subject'   => 'Reset your password',
                'body_html' => $this->html('Password Reset', '<p>Hi {{user_name}},</p><p>We received a request to reset your password. Click the link below to set a new password:</p><p style="text-align:center;margin:30px 0"><a href="{{reset_url}}" style="background-color:#3498db;color:#fff;padding:12px 30px;text-decoration:none;border-radius:5px;display:inline-block">Reset Password</a></p><p>This link will expire in {{expiry_minutes}} minutes.</p><p>If you did not request a password reset, please ignore this email.</p>'),
                'body_text' => $this->text("Password Reset\n\nHi {{user_name}},\n\nWe received a request to reset your password. Visit the link below to set a new password:\n\n{{reset_url}}\n\nThis link will expire in {{expiry_minutes}} minutes.\n\nIf you did not request a password reset, please ignore this email."),
                'variables' => ['user_name', 'reset_url', 'expiry_minutes'],
                'is_system' => true,
                'status'    => Status::ACTIVE,
            ],

            // ── Order ────────────────────────────────────────
            [
                'slug'      => 'order-confirmation',
                'name'      => 'Order Confirmation',
                'category'  => 'order',
                'subject'   => 'Order #{{order_number}} confirmed',
                'body_html' => $this->html('Order Confirmed', '<p>Hi {{user_name}},</p><p>Thank you for your order! We have received your order and it is now being processed.</p><div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0"><p><strong>Order Number:</strong> #{{order_number}}</p><p><strong>Order Date:</strong> {{order_date}}</p><p><strong>Total:</strong> {{order_total}}</p></div>{{order_items_html}}<p>You will receive another email when your order has been shipped.</p>'),
                'body_text' => $this->text("Order Confirmed\n\nHi {{user_name}},\n\nThank you for your order!\n\nOrder Number: #{{order_number}}\nOrder Date: {{order_date}}\nTotal: {{order_total}}\n\n{{order_items_text}}\n\nYou will receive another email when your order has been shipped."),
                'variables' => ['user_name', 'order_number', 'order_date', 'order_total', 'order_items_html', 'order_items_text'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],
            [
                'slug'      => 'order-shipped',
                'name'      => 'Order Shipped',
                'category'  => 'order',
                'subject'   => 'Order #{{order_number}} has been shipped',
                'body_html' => $this->html('Order Shipped', '<p>Hi {{user_name}},</p><p>Your order has been shipped!</p><div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0"><p><strong>Order Number:</strong> #{{order_number}}</p><p><strong>Carrier:</strong> {{carrier}}</p><p><strong>Tracking Number:</strong> {{tracking_number}}</p></div><p>You can track your shipment using the tracking number above.</p>'),
                'body_text' => $this->text("Order Shipped\n\nHi {{user_name}},\n\nYour order has been shipped!\n\nOrder Number: #{{order_number}}\nCarrier: {{carrier}}\nTracking Number: {{tracking_number}}\n\nYou can track your shipment using the tracking number above."),
                'variables' => ['user_name', 'order_number', 'carrier', 'tracking_number'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],
            [
                'slug'      => 'order-cancelled',
                'name'      => 'Order Cancelled',
                'category'  => 'order',
                'subject'   => 'Order #{{order_number}} has been cancelled',
                'body_html' => $this->html('Order Cancelled', '<p>Hi {{user_name}},</p><p>Your order #{{order_number}} has been cancelled.</p><div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0"><p><strong>Order Number:</strong> #{{order_number}}</p><p><strong>Reason:</strong> {{cancellation_reason}}</p></div><p>If you have any questions, please contact our support team.</p>'),
                'body_text' => $this->text("Order Cancelled\n\nHi {{user_name}},\n\nYour order #{{order_number}} has been cancelled.\n\nReason: {{cancellation_reason}}\n\nIf you have any questions, please contact our support team."),
                'variables' => ['user_name', 'order_number', 'cancellation_reason'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],
            [
                'slug'      => 'refund-processed',
                'name'      => 'Refund Processed',
                'category'  => 'order',
                'subject'   => 'Refund for Order #{{order_number}} processed',
                'body_html' => $this->html('Refund Processed', '<p>Hi {{user_name}},</p><p>Your refund has been processed successfully.</p><div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0"><p><strong>Order Number:</strong> #{{order_number}}</p><p><strong>Refund Amount:</strong> {{refund_amount}}</p><p><strong>Refund Method:</strong> {{refund_method}}</p></div><p>Please allow 5–10 business days for the refund to appear on your statement.</p>'),
                'body_text' => $this->text("Refund Processed\n\nHi {{user_name}},\n\nYour refund has been processed successfully.\n\nOrder Number: #{{order_number}}\nRefund Amount: {{refund_amount}}\nRefund Method: {{refund_method}}\n\nPlease allow 5-10 business days for the refund to appear on your statement."),
                'variables' => ['user_name', 'order_number', 'refund_amount', 'refund_method'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],

            // ── Form ─────────────────────────────────────────
            [
                'slug'      => 'form-submission-staff-notification',
                'name'      => 'Form Submission - Staff Notification',
                'category'  => 'form',
                'subject'   => 'New {{form_name}} Submission from {{user_name}}',
                'body_html' => $this->html('New Form Submission Received', '<p>A new form submission has been received and requires your attention.</p><h3 style="color:#2c3e50;border-bottom:2px solid #3498db;padding-bottom:10px">Form Submission Details</h3>{{form_fields_html}}'),
                'body_text' => $this->text("New Form Submission Received\n\nA new form submission has been received and requires your attention.\n\nFORM: {{form_name}}\n\nSUBMISSION DETAILS\n-------------------\n{{form_fields_text}}"),
                'variables' => ['form_name', 'user_name', 'form_fields_html', 'form_fields_text'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],
            [
                'slug'      => 'form-submission-confirmation',
                'name'      => 'Form Submission - User Confirmation',
                'category'  => 'form',
                'subject'   => 'We received your {{form_name}} submission',
                'body_html' => $this->html('Submission Received', '<p>Hi {{user_name}},</p><p>Thank you for your submission. We have received your <strong>{{form_name}}</strong> and will get back to you shortly.</p><p>For your records, here is a copy of your submission:</p>{{form_fields_html}}'),
                'body_text' => $this->text("Submission Received\n\nHi {{user_name}},\n\nThank you for your submission. We have received your {{form_name}} and will get back to you shortly.\n\nFor your records:\n\n{{form_fields_text}}"),
                'variables' => ['form_name', 'user_name', 'form_fields_html', 'form_fields_text'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],

            // ── Review ───────────────────────────────────────
            [
                'slug'      => 'review-approved',
                'name'      => 'Product Review Approved',
                'category'  => 'review',
                'subject'   => 'Your review for {{product_name}} has been published',
                'body_html' => $this->html('Review Published', '<p>Hi {{user_name}},</p><p>Your review for <strong>{{product_name}}</strong> has been approved and is now live on our site.</p><p>Thank you for sharing your feedback!</p>'),
                'body_text' => $this->text("Review Published\n\nHi {{user_name}},\n\nYour review for {{product_name}} has been approved and is now live on our site.\n\nThank you for sharing your feedback!"),
                'variables' => ['user_name', 'product_name'],
                'is_system' => false,
                'status'    => Status::ACTIVE,
            ],

            // ── System ───────────────────────────────────────
            [
                'slug'      => 'new-admin-login',
                'name'      => 'Admin Login Alert',
                'category'  => 'system',
                'subject'   => '[{{site_name}}] Admin login from {{ip_address}}',
                'body_html' => $this->html('Admin Login Detected', '<p>A new admin login was detected:</p><div style="background:#f8f9fa;padding:15px;border-radius:5px;margin:20px 0"><p><strong>User:</strong> {{admin_name}}</p><p><strong>IP Address:</strong> {{ip_address}}</p><p><strong>Time:</strong> {{login_time}}</p><p><strong>Browser:</strong> {{user_agent}}</p></div><p>If this was not you, please change your password immediately.</p>'),
                'body_text' => $this->text("Admin Login Detected\n\nUser: {{admin_name}}\nIP Address: {{ip_address}}\nTime: {{login_time}}\nBrowser: {{user_agent}}\n\nIf this was not you, please change your password immediately."),
                'variables' => ['site_name', 'admin_name', 'ip_address', 'login_time', 'user_agent'],
                'is_system' => true,
                'status'    => Status::ACTIVE,
            ],
        ];
    }

    // ─── Template Helpers ────────────────────────────────────

    private function html(string $heading, string $body): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .header h2 { color: #2c3e50; margin: 0; }
                .content { background: #fff; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; margin-bottom: 20px; }
                .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center; color: #999; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header"><h2>{$heading}</h2></div>
            <div class="content">{$body}</div>
            <div class="footer"><p>This is an automated notification. Please do not reply to this email.</p></div>
        </body>
        </html>
        HTML;
    }

    private function text(string $body): string
    {
        return $body . "\n\n---\nThis is an automated notification. Please do not reply to this email.";
    }
}
