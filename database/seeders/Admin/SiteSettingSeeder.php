<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders\Admin;

use Appsolutely\AIO\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::getConfigDefinitions() as $config) {
            SiteSetting::firstOrCreate(
                ['key' => $config['key']],
                [
                    'group' => $config['group'],
                    'value' => $config['default'],
                    'type'  => $config['type'],
                ]
            );
        }
    }

    /**
     * Get config definitions for site_settings table.
     */
    public static function getConfigDefinitions(): array
    {
        return [
            // General
            ['group' => 'general', 'key' => 'general.site_name',   'type' => 'string', 'default' => 'appsolutely'],
            ['group' => 'general', 'key' => 'general.site_title',  'type' => 'string', 'default' => 'Appsolutely'],
            ['group' => 'general', 'key' => 'general.timezone',    'type' => 'string', 'default' => 'Pacific/Auckland'],
            ['group' => 'general', 'key' => 'general.date_format', 'type' => 'string', 'default' => 'Y-m-d'],
            ['group' => 'general', 'key' => 'general.time_format', 'type' => 'string', 'default' => 'H:i:s'],
            ['group' => 'general', 'key' => 'general.locale',      'type' => 'string', 'default' => 'en'],
            ['group' => 'general', 'key' => 'general.copyright',   'type' => 'string', 'default' => null],
            ['group' => 'general', 'key' => 'general.tagline',     'type' => 'string', 'default' => null],

            // SEO
            ['group' => 'seo', 'key' => 'seo.meta_keywords',    'type' => 'text', 'default' => 'Appsolutely, Software as a service solution, SAAS solution'],
            ['group' => 'seo', 'key' => 'seo.meta_description',  'type' => 'text', 'default' => 'Appsolutely is a SAAS platform to help developer build up their applications.'],
            ['group' => 'seo', 'key' => 'seo.site_meta',         'type' => 'text', 'default' => null],
            ['group' => 'seo', 'key' => 'seo.structured_data',   'type' => 'text', 'default' => null],
            ['group' => 'seo', 'key' => 'seo.tracking_code',     'type' => 'text', 'default' => null],
            ['group' => 'seo', 'key' => 'seo.noscript',                  'type' => 'text',   'default' => null],
            ['group' => 'seo', 'key' => 'seo.og_image',                  'type' => 'image',  'default' => null],
            ['group' => 'seo', 'key' => 'seo.robots',                    'type' => 'string', 'default' => 'index, follow'],
            ['group' => 'seo', 'key' => 'seo.google_site_verification',  'type' => 'string', 'default' => null],

            // Appearance
            ['group' => 'appearance', 'key' => 'appearance.theme',           'type' => 'string', 'default' => 'appsolutely'],
            ['group' => 'appearance', 'key' => 'appearance.logo_light',       'type' => 'image',  'default' => 'images/logo.jpg'],
            ['group' => 'appearance', 'key' => 'appearance.logo_dark',        'type' => 'image',  'default' => null],
            ['group' => 'appearance', 'key' => 'appearance.favicon',          'type' => 'image',  'default' => 'images/icon.jpg'],
            ['group' => 'appearance', 'key' => 'appearance.logo_pattern',     'type' => 'string', 'default' => 'images/logo.%s'],
            ['group' => 'appearance', 'key' => 'appearance.favicon_pattern',  'type' => 'string', 'default' => 'images/icon.%s'],
            ['group' => 'appearance', 'key' => 'appearance.primary_color',    'type' => 'string', 'default' => null],
            ['group' => 'appearance', 'key' => 'appearance.secondary_color',  'type' => 'string', 'default' => null],
            ['group' => 'appearance', 'key' => 'appearance.custom_css',       'type' => 'text',   'default' => null],

            // Social
            ['group' => 'social', 'key' => 'social.facebook',   'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.twitter',    'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.instagram',  'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.linkedin',   'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.youtube',    'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.tiktok',     'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.pinterest',  'type' => 'string', 'default' => null],
            ['group' => 'social', 'key' => 'social.whatsapp',   'type' => 'string', 'default' => null],

            // Forms / Captcha
            ['group' => 'forms', 'key' => 'forms.captcha.honeypot.enabled',  'type' => 'boolean', 'default' => '1'],
            ['group' => 'forms', 'key' => 'forms.captcha.honeypot.min_time', 'type' => 'integer', 'default' => '3'],
            ['group' => 'forms', 'key' => 'forms.captcha.turnstile.enabled',    'type' => 'boolean', 'default' => '0'],
            ['group' => 'forms', 'key' => 'forms.captcha.turnstile.site_key',   'type' => 'string',  'default' => null],
            ['group' => 'forms', 'key' => 'forms.captcha.turnstile.secret_key', 'type' => 'string',  'default' => null],

            // Contact
            ['group' => 'contact', 'key' => 'contact.email',           'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.phone',           'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.address',         'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.city',            'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.country',         'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.postal_code',     'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.google_maps_url', 'type' => 'string', 'default' => null],
            ['group' => 'contact', 'key' => 'contact.business_hours',  'type' => 'json',   'default' => null],
        ];
    }
}
