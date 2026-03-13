<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Mapping from old admin_config keys to new site_settings (group, key, type).
     */
    private const KEY_MAP = [
        'basic.name'           => ['group' => 'general',    'key' => 'general.site_name',      'type' => 'string'],
        'basic.title'          => ['group' => 'general',    'key' => 'general.site_title',      'type' => 'string'],
        'basic.timezone'       => ['group' => 'general',    'key' => 'general.timezone',        'type' => 'string'],
        'basic.dateFormat'     => ['group' => 'general',    'key' => 'general.date_format',     'type' => 'string'],
        'basic.timeFormat'     => ['group' => 'general',    'key' => 'general.time_format',     'type' => 'string'],
        'basic.locale'         => ['group' => 'general',    'key' => 'general.locale',          'type' => 'string'],
        'basic.copyright'      => ['group' => 'general',    'key' => 'general.copyright',       'type' => 'string'],
        'basic.keywords'       => ['group' => 'seo',        'key' => 'seo.meta_keywords',       'type' => 'text'],
        'basic.description'    => ['group' => 'seo',        'key' => 'seo.meta_description',    'type' => 'text'],
        'basic.siteMeta'       => ['group' => 'seo',        'key' => 'seo.site_meta',           'type' => 'text'],
        'basic.structuredData' => ['group' => 'seo',        'key' => 'seo.structured_data',     'type' => 'text'],
        'basic.trackingCode'   => ['group' => 'seo',        'key' => 'seo.tracking_code',       'type' => 'text'],
        'basic.noscript'       => ['group' => 'seo',        'key' => 'seo.noscript',            'type' => 'text'],
        'basic.theme'          => ['group' => 'appearance', 'key' => 'appearance.theme',         'type' => 'string'],
        'basic.logo'           => ['group' => 'appearance', 'key' => 'appearance.logo_light',    'type' => 'image'],
        'basic.favicon'        => ['group' => 'appearance', 'key' => 'appearance.favicon',       'type' => 'image'],
        'basic.logoPattern'    => ['group' => 'appearance', 'key' => 'appearance.logo_pattern',  'type' => 'string'],
        'basic.faviconPattern' => ['group' => 'appearance', 'key' => 'appearance.favicon_pattern', 'type' => 'string'],
        'mail.server'          => ['group' => 'mail',       'key' => 'mail.host',               'type' => 'string'],
        'mail.port'            => ['group' => 'mail',       'key' => 'mail.port',               'type' => 'string'],
        'mail.username'        => ['group' => 'mail',       'key' => 'mail.username',           'type' => 'string'],
        'mail.password'        => ['group' => 'mail',       'key' => 'mail.password',           'type' => 'string'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        $existing = DB::table('admin_settings')
            ->where('slug', 'ghost::admin_config')
            ->value('value');

        if (! $existing) {
            return;
        }

        $configs = json_decode($existing, true);

        if (! is_array($configs)) {
            return;
        }

        $oldValues = collect($configs)->pluck('value', 'key')->toArray();

        $now = now();

        foreach (self::KEY_MAP as $oldKey => $mapping) {
            $value = $oldValues[$oldKey] ?? null;

            DB::table('site_settings')->updateOrInsert(
                ['key' => $mapping['key']],
                [
                    'group'      => $mapping['group'],
                    'value'      => $value,
                    'type'       => $mapping['type'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        $keys = array_column(self::KEY_MAP, 'key');

        DB::table('site_settings')->whereIn('key', $keys)->delete();
    }
};
