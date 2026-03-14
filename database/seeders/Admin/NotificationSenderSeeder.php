<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders\Admin;

use Appsolutely\AIO\Models\NotificationSender;
use Illuminate\Database\Seeder;

class NotificationSenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Internal sender - for staff/admin notifications
        NotificationSender::updateOrCreate(
            ['slug' => 'internal-default'],
            [
                'name'         => 'Internal Staff Mailer',
                'type'         => 'smtp',
                'category'     => 'internal',
                'from_address' => 'noreply@internal.company.com',
                'from_name'    => 'Internal System',
                'is_default'   => true,
                'priority'     => 10,
                'is_active'    => true,
                'description'  => 'Default sender for internal staff and admin notifications',
            ]
        );

        // External sender - for customer/public notifications
        NotificationSender::updateOrCreate(
            ['slug' => 'external-default'],
            [
                'name'         => 'Customer Transactional',
                'type'         => 'smtp',
                'category'     => 'external',
                'from_address' => 'noreply@company.com',
                'from_name'    => 'Company Name',
                'is_default'   => true,
                'priority'     => 10,
                'is_active'    => true,
                'description'  => 'Default sender for customer and public-facing notifications',
            ]
        );

        // System sender - for system alerts
        NotificationSender::updateOrCreate(
            ['slug' => 'system-default'],
            [
                'name'         => 'System Alerts',
                'type'         => 'log',
                'category'     => 'system',
                'from_address' => 'system@company.com',
                'from_name'    => 'System',
                'is_default'   => true,
                'priority'     => 10,
                'is_active'    => true,
                'description'  => 'Default sender for system alerts and error notifications',
            ]
        );
    }
}
