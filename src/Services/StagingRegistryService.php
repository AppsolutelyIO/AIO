<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Http\Middleware\StagingAccessGate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;

/**
 * Manages a shared registry of staging/preview environments.
 *
 * Each staging app registers itself via heartbeat. Stale entries
 * (no heartbeat within the TTL) are cleaned up automatically.
 * Tokens are derived from the URL — no need to store them.
 */
class StagingRegistryService
{
    private const REDIS_KEY = 'aio:staging_registry';

    private const STALE_THRESHOLD_MINUTES = 120;

    /**
     * Register or refresh the current environment in the registry.
     */
    public function heartbeat(): void
    {
        $url = config('app.url');

        if (empty($url)) {
            return;
        }

        $data = json_encode([
            'name'      => config('app.name', 'Staging'),
            'last_seen' => Carbon::now()->toIso8601String(),
        ]);

        Redis::hset(self::REDIS_KEY, $url, $data);
    }

    /**
     * Remove the current environment from the registry.
     */
    public function deregister(): void
    {
        Redis::hdel(self::REDIS_KEY, config('app.url'));
    }

    /**
     * List all registered staging environments.
     *
     * @return array<int, array{url: string, access_url: string, name: string, last_seen: string|null}>
     */
    public function list(): array
    {
        $entries = Redis::hgetall(self::REDIS_KEY);

        $results = [];

        foreach ($entries as $url => $json) {
            $data = json_decode($json, true);

            if (! is_array($data)) {
                continue;
            }

            $token = StagingAccessGate::generateToken($url);

            $results[] = [
                'url'        => $url,
                'access_url' => $url . '?token=' . $token,
                'name'       => $data['name'] ?? 'Unknown',
                'last_seen'  => $data['last_seen'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * Remove entries that haven't sent a heartbeat within the threshold.
     *
     * @return int Number of entries removed
     */
    public function cleanup(): int
    {
        $entries = Redis::hgetall(self::REDIS_KEY);
        $cutoff  = Carbon::now()->subMinutes(self::STALE_THRESHOLD_MINUTES);
        $removed = 0;

        foreach ($entries as $url => $json) {
            $data = json_decode($json, true);

            if (! is_array($data) || empty($data['last_seen'])) {
                Redis::hdel(self::REDIS_KEY, $url);
                $removed++;

                continue;
            }

            if (Carbon::parse($data['last_seen'])->lt($cutoff)) {
                Redis::hdel(self::REDIS_KEY, $url);
                $removed++;
            }
        }

        return $removed;
    }
}
