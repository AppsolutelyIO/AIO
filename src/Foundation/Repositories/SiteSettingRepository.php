<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Foundation\Repositories;

use Appsolutely\AIO\Models\SiteSetting;

class SiteSettingRepository extends BaseRepository
{
    public function model(): string
    {
        return SiteSetting::class;
    }

    /**
     * Get a single setting value by key.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->findByFieldFirst('key', $key);

        return $setting?->typed_value ?? $default;
    }

    /**
     * Get all settings for a group as key => value array.
     */
    public function getGroup(string $group): array
    {
        return $this->findByField('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get all settings as a flat key => typed_value array.
     */
    public function allAsArray(): array
    {
        return $this->all()
            ->mapWithKeys(fn (SiteSetting $setting) => [$setting->key => $setting->typed_value])
            ->toArray();
    }

    /**
     * Set a setting value (upsert by key).
     */
    public function setValue(string $key, mixed $value, ?string $group = null, ?string $type = null): SiteSetting
    {
        $attributes = ['value' => is_array($value) ? json_encode($value) : (string) $value];

        if ($group !== null) {
            $attributes['group'] = $group;
        }

        if ($type !== null) {
            $attributes['type'] = $type;
        }

        return SiteSetting::updateOrCreate(
            ['key' => $key],
            $attributes,
        );
    }

    /**
     * Bulk set settings.
     *
     * @param  array<string, mixed>  $settings  key => value pairs
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->setValue($key, $value);
        }
    }
}
