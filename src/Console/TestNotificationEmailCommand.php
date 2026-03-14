<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Appsolutely\AIO\Services\NotificationSenderService;
use Illuminate\Console\Command;

final class TestNotificationEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:test-email
                            {email : Email address to send test to}
                            {--sender= : Sender ID to test (default: uses default sender)}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test email to verify SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle(
        NotificationSenderService $senderService,
        NotificationSenderRepository $senderRepository
    ): int {
        $email    = $this->argument('email');
        $senderId = $this->option('sender');

        $this->info('Testing email configuration...');
        $this->newLine();

        try {
            // Get sender
            if ($senderId) {
                $sender = NotificationSender::find($senderId);
                if (! $sender) {
                    $this->error("Sender with ID {$senderId} not found");

                    return self::FAILURE;
                }
            } else {
                $sender = $senderRepository->getDefaultForCategory('external');
                if (! $sender) {
                    $this->error('No default sender configured');

                    return self::FAILURE;
                }
            }

            $this->info("Using sender: {$sender->name}");
            $this->line("SMTP Host: {$sender->smtp_host}");
            $this->line("SMTP Port: {$sender->smtp_port}");
            $this->line("SMTP Username: {$sender->smtp_username}");
            $this->newLine();

            // Get mailer and from address
            $mailer = $senderService->getMailer($sender);
            $from   = $senderService->getFromAddress($sender);

            // Send test email
            $this->info('Sending test email...');

            $mailer->send([], [], function ($message) use ($email, $from) {
                $message->from($from['address'], $from['name'])
                    ->to($email)
                    ->subject('Test Email from Notification System')
                    ->html('<h1>Test Email</h1><p>If you received this email, your SMTP configuration is working correctly!</p><p>Sent at: ' . now() . '</p>');
            });

            $this->newLine();
            $this->info('✓ Test email sent successfully!');
            $this->comment("Check inbox at: {$email}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Failed to send test email');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();

            // Show helpful hints
            $this->comment('Common issues:');
            $this->line('  • Gmail: Use App Password instead of regular password');
            $this->line('  • Generate at: https://myaccount.google.com/apppasswords');
            $this->line('  • Check SMTP host and port are correct');
            $this->line('  • Ensure 2-Factor Authentication is enabled on Gmail');

            return self::FAILURE;
        }
    }
}
