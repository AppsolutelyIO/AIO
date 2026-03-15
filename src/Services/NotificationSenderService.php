<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Mail;

final readonly class NotificationSenderService
{
    public function __construct(
        private NotificationSenderRepository $senderRepository
    ) {}

    public function getSenderForRule(NotificationRule $rule, ?array $data = null): ?NotificationSender
    {
        if ($rule->sender_id) {
            $sender = $this->senderRepository->find($rule->sender_id);
            if ($sender && $sender->is_active) {
                return $sender;
            }
        }

        $category = $this->resolveCategory($rule->recipient_type, $data);

        return $this->senderRepository->getDefaultForCategory($category);
    }

    private function resolveCategory(string $recipientType, ?array $data): string
    {
        return match ($recipientType) {
            'admin'       => 'internal',
            'user'        => 'external',
            'custom'      => $this->determineCustomCategory($data),
            'conditional' => $this->determineConditionalCategory($data),
            default       => 'external',
        };
    }

    private function determineCustomCategory(?array $data): string
    {
        if (isset($data['recipient_email'])) {
            return $this->isInternalEmail($data['recipient_email']) ? 'internal' : 'external';
        }

        return 'external';
    }

    private function determineConditionalCategory(?array $data): string
    {
        return $this->determineCustomCategory($data);
    }

    private function isInternalEmail(string $email): bool
    {
        $internalDomains = config('notifications.internal_domains', [
            '@company.com',
            '@internal.company.com',
            '@staff.company.com',
        ]);

        foreach ($internalDomains as $domain) {
            if (str_ends_with($email, $domain)) {
                return true;
            }
        }

        return false;
    }

    public function configureMailer(NotificationSender $sender): void
    {
        $mailerName = "sender_{$sender->id}";

        config([
            "mail.mailers.{$mailerName}" => $this->buildMailerConfig($sender),
        ]);
    }

    private function buildMailerConfig(NotificationSender $sender): array
    {
        $config = ['transport' => $sender->type];

        if ($sender->type === 'smtp') {
            $config = array_merge($config, [
                'host'         => $sender->smtp_host,
                'port'         => $sender->smtp_port ?? 587,
                'username'     => $sender->smtp_username,
                'password'     => $sender->decrypted_password,
                'encryption'   => $sender->smtp_encryption,
                'timeout'      => null,
                'local_domain' => parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST),
            ]);
        }

        if ($serviceConfig = $sender->decrypted_service_config) {
            $this->applyServiceConfig($config, $sender->type, $serviceConfig);
        }

        return $config;
    }

    private function applyServiceConfig(array &$config, string $type, array $serviceConfig): void
    {
        match ($type) {
            'mailgun'  => $this->configureMailgun($config, $serviceConfig),
            'ses'      => $this->configureSes($config, $serviceConfig),
            'postmark' => $this->configurePostmark($config, $serviceConfig),
            'resend'   => $this->configureResend($config, $serviceConfig),
            default    => null,
        };
    }

    private function configureMailgun(array &$config, array $serviceConfig): void
    {
        if (isset($serviceConfig['domain'])) {
            config(['services.mailgun.domain' => $serviceConfig['domain']]);
        }
        if (isset($serviceConfig['secret'])) {
            config(['services.mailgun.secret' => $serviceConfig['secret']]);
        }
    }

    private function configureSes(array &$config, array $serviceConfig): void
    {
        if (isset($serviceConfig['key'])) {
            config(['services.ses.key' => $serviceConfig['key']]);
        }
        if (isset($serviceConfig['secret'])) {
            config(['services.ses.secret' => $serviceConfig['secret']]);
        }
        if (isset($serviceConfig['region'])) {
            config(['services.ses.region' => $serviceConfig['region']]);
        } else {
            config(['services.ses.region' => 'us-east-1']);
        }
    }

    private function configurePostmark(array &$config, array $serviceConfig): void
    {
        if (isset($serviceConfig['token'])) {
            config(['services.postmark.token' => $serviceConfig['token']]);
        }
    }

    private function configureResend(array &$config, array $serviceConfig): void
    {
        if (isset($serviceConfig['key'])) {
            config(['services.resend.key' => $serviceConfig['key']]);
        }
    }

    public function getMailer(NotificationSender $sender): Mailer
    {
        $this->configureMailer($sender);
        $mailerName = "sender_{$sender->id}";

        return Mail::mailer($mailerName);
    }

    public function getFromAddress(NotificationSender $sender): array
    {
        return [
            'address' => $sender->from_address,
            'name'    => $sender->from_name ?? config('app.name'),
        ];
    }
}
