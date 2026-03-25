<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Services\Contracts\TurnstileServiceInterface;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;

final readonly class TurnstileService implements TurnstileServiceInterface
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        protected LoggerInterface $logger
    ) {}

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if ($token === '') {
            $this->logger->info('Turnstile: empty token received', ['ip' => $remoteIp]);

            return false;
        }

        try {
            $payload = [
                'secret'   => config('forms.captcha.turnstile.secret_key'),
                'response' => $token,
            ];

            if ($remoteIp !== null && $remoteIp !== '') {
                $payload['remoteip'] = $remoteIp;
            }

            $response = Http::asForm()
                ->timeout(5)
                ->post(self::VERIFY_URL, $payload);

            if (! $response->successful()) {
                $this->logger->warning('Turnstile: API request failed', [
                    'status' => $response->status(),
                    'ip'     => $remoteIp,
                ]);

                return false;
            }

            $result = $response->json();

            if (! ($result['success'] ?? false)) {
                $this->logger->info('Turnstile: verification failed', [
                    'error-codes' => $result['error-codes'] ?? [],
                    'ip'          => $remoteIp,
                ]);

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Turnstile: exception during verification', [
                'error' => $e->getMessage(),
                'ip'    => $remoteIp,
            ]);

            // Fail open on network errors to avoid blocking legitimate users
            return true;
        }
    }

    public function isEnabled(): bool
    {
        return (bool) config('forms.captcha.turnstile.enabled', false)
            && config('forms.captcha.turnstile.secret_key', '') !== '';
    }
}
