<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Http\Middleware\StagingAccessGate;
use Appsolutely\AIO\Services\StagingRegistryService;
use Illuminate\Console\Command;

/**
 * Display the staging access token and trigger a registry heartbeat.
 */
final class StagingTokenCommand extends Command
{
    protected $signature = 'staging:token';

    protected $description = 'Show the staging access token and send a registry heartbeat';

    public function handle(StagingRegistryService $registry): int
    {
        if (! config('aio.staging_access_enabled')) {
            $this->warn('Staging access gate is not enabled (STAGING_ACCESS_ENABLED=false).');

            return self::FAILURE;
        }

        $token   = StagingAccessGate::generateToken();
        $baseUrl = config('app.url');

        $this->components->twoColumnDetail('Token', $token);
        $this->components->twoColumnDetail('Access URL', $baseUrl . '?token=' . $token);
        $this->components->twoColumnDetail('Registry API', $baseUrl . '/api/staging-registry?token=' . $token);

        $registry->heartbeat();
        $this->newLine();
        $this->info('Heartbeat sent to staging registry.');

        return self::SUCCESS;
    }
}
