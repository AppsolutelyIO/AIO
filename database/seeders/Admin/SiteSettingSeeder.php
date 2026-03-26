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
            SiteSetting::updateOrCreate(
                ['key' => $config['key']],
                [
                    'group'       => $config['group'],
                    'label'       => $config['label'] ?? null,
                    'description' => $config['description'] ?? null,
                    'type'        => $config['type'],
                    // Only set value on first creation, don't overwrite existing values
                    ...(! SiteSetting::where('key', $config['key'])->exists()
                        ? ['value' => $config['default']]
                        : []),
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
            ['group' => 'general', 'key' => 'general.site_name',   'type' => 'string', 'default' => 'appsolutely', 'label' => 'Site Name',   'description' => 'Internal site identifier used in config and URLs'],
            ['group' => 'general', 'key' => 'general.site_title',  'type' => 'string', 'default' => 'Appsolutely', 'label' => 'Site Title',  'description' => 'Display name shown in browser tab and page headers'],
            ['group' => 'general', 'key' => 'general.timezone',    'type' => 'string', 'default' => 'Pacific/Auckland', 'label' => 'Timezone', 'description' => 'Timezone for date/time display (e.g. Pacific/Auckland)'],
            ['group' => 'general', 'key' => 'general.date_format', 'type' => 'string', 'default' => 'Y-m-d',      'label' => 'Date Format', 'description' => 'PHP date format string (e.g. Y-m-d, d/m/Y)'],
            ['group' => 'general', 'key' => 'general.time_format', 'type' => 'string', 'default' => 'H:i:s',      'label' => 'Time Format', 'description' => 'PHP time format string (e.g. H:i:s, g:i A)'],
            ['group' => 'general', 'key' => 'general.locale',      'type' => 'string', 'default' => 'en',          'label' => 'Locale',      'description' => 'Language code for translations (e.g. en, zh)'],
            ['group' => 'general', 'key' => 'general.copyright',   'type' => 'string', 'default' => null,          'label' => 'Copyright',   'description' => 'Copyright text displayed in the footer'],
            ['group' => 'general', 'key' => 'general.tagline',     'type' => 'string', 'default' => null,          'label' => 'Tagline',     'description' => 'Short tagline displayed alongside the site name'],

            // SEO
            ['group' => 'seo', 'key' => 'seo.meta_keywords',              'type' => 'text',   'default' => 'Appsolutely, Software as a service solution, SAAS solution', 'label' => 'Meta Keywords',              'description' => 'Comma-separated keywords for search engines'],
            ['group' => 'seo', 'key' => 'seo.meta_description',            'type' => 'text',   'default' => 'Appsolutely is a SAAS platform to help developer build up their applications.', 'label' => 'Meta Description', 'description' => 'Default meta description for pages without their own'],
            ['group' => 'seo', 'key' => 'seo.site_meta',                   'type' => 'text',   'default' => null, 'label' => 'Site Meta Tags',            'description' => 'Additional HTML meta tags injected into <head>'],
            ['group' => 'seo', 'key' => 'seo.structured_data',             'type' => 'text',   'default' => null, 'label' => 'Structured Data',           'description' => 'JSON-LD structured data for rich search results'],
            ['group' => 'seo', 'key' => 'seo.tracking_code',               'type' => 'text',   'default' => null, 'label' => 'Tracking Code',             'description' => 'Analytics/tracking scripts injected into <head>'],
            ['group' => 'seo', 'key' => 'seo.noscript',                    'type' => 'text',   'default' => null, 'label' => 'Noscript Tags',             'description' => 'HTML inserted inside <noscript> for tracking fallbacks'],
            ['group' => 'seo', 'key' => 'seo.og_image',                    'type' => 'image',  'default' => null, 'label' => 'OG Image',                  'description' => 'Default Open Graph image for social media sharing'],
            ['group' => 'seo', 'key' => 'seo.robots',                      'type' => 'string', 'default' => 'index, follow', 'label' => 'Robots',         'description' => 'Default robots meta directive (e.g. index, follow)'],
            ['group' => 'seo', 'key' => 'seo.google_site_verification',    'type' => 'string', 'default' => null, 'label' => 'Google Site Verification',  'description' => 'Google Search Console verification code'],

            // Appearance
            ['group' => 'appearance', 'key' => 'appearance.theme',           'type' => 'string', 'default' => 'appsolutely',  'label' => 'Theme',           'description' => 'Active frontend theme name'],
            ['group' => 'appearance', 'key' => 'appearance.logo_light',       'type' => 'image',  'default' => 'images/logo.jpg', 'label' => 'Logo (Light)',  'description' => 'Logo displayed on dark backgrounds'],
            ['group' => 'appearance', 'key' => 'appearance.logo_dark',        'type' => 'image',  'default' => null,           'label' => 'Logo (Dark)',     'description' => 'Logo displayed on light backgrounds'],
            ['group' => 'appearance', 'key' => 'appearance.favicon',          'type' => 'image',  'default' => 'images/icon.jpg', 'label' => 'Favicon',      'description' => 'Browser tab icon'],
            ['group' => 'appearance', 'key' => 'appearance.logo_pattern',     'type' => 'string', 'default' => 'images/logo.%s',  'label' => 'Logo Pattern', 'description' => 'Filename pattern for logo variants (use %s for extension)'],
            ['group' => 'appearance', 'key' => 'appearance.favicon_pattern',  'type' => 'string', 'default' => 'images/icon.%s',  'label' => 'Favicon Pattern', 'description' => 'Filename pattern for favicon variants (use %s for extension)'],
            ['group' => 'appearance', 'key' => 'appearance.primary_color',    'type' => 'string', 'default' => null,           'label' => 'Primary Color',   'description' => 'Brand primary color (hex, e.g. #1a73e8)'],
            ['group' => 'appearance', 'key' => 'appearance.secondary_color',  'type' => 'string', 'default' => null,           'label' => 'Secondary Color', 'description' => 'Brand secondary color (hex, e.g. #ff6b35)'],
            ['group' => 'appearance', 'key' => 'appearance.custom_css',       'type' => 'text',   'default' => null,           'label' => 'Custom CSS',      'description' => 'Additional CSS injected on every page'],

            // Social
            ['group' => 'social', 'key' => 'social.facebook',   'type' => 'string', 'default' => null, 'label' => 'Facebook',   'description' => 'Facebook page URL'],
            ['group' => 'social', 'key' => 'social.twitter',    'type' => 'string', 'default' => null, 'label' => 'Twitter / X', 'description' => 'Twitter/X profile URL'],
            ['group' => 'social', 'key' => 'social.instagram',  'type' => 'string', 'default' => null, 'label' => 'Instagram',  'description' => 'Instagram profile URL'],
            ['group' => 'social', 'key' => 'social.linkedin',   'type' => 'string', 'default' => null, 'label' => 'LinkedIn',   'description' => 'LinkedIn company page URL'],
            ['group' => 'social', 'key' => 'social.youtube',    'type' => 'string', 'default' => null, 'label' => 'YouTube',    'description' => 'YouTube channel URL'],
            ['group' => 'social', 'key' => 'social.tiktok',     'type' => 'string', 'default' => null, 'label' => 'TikTok',     'description' => 'TikTok profile URL'],
            ['group' => 'social', 'key' => 'social.pinterest',  'type' => 'string', 'default' => null, 'label' => 'Pinterest',  'description' => 'Pinterest profile URL'],
            ['group' => 'social', 'key' => 'social.whatsapp',   'type' => 'string', 'default' => null, 'label' => 'WhatsApp',   'description' => 'WhatsApp number or link'],

            // Forms / Captcha — Honeypot
            ['group' => 'forms', 'key' => 'forms.captcha.honeypot.enabled',        'type' => 'boolean', 'default' => '1', 'label' => 'Enabled',            'description' => 'Invisible hidden field that catches spam bots'],
            ['group' => 'forms', 'key' => 'forms.captcha.honeypot.min_time',       'type' => 'integer', 'default' => '3', 'label' => 'Min Time (seconds)', 'description' => 'Minimum seconds before submission is accepted (too-fast = bot)'],

            // Forms / Captcha — Turnstile
            ['group' => 'forms', 'key' => 'forms.captcha.turnstile.enabled',       'type' => 'boolean', 'default' => '0', 'label' => 'Enabled',    'description' => 'Cloudflare Turnstile human verification widget. Get keys at dash.cloudflare.com > Turnstile'],
            ['group' => 'forms', 'key' => 'forms.captcha.turnstile.site_key',      'type' => 'string',  'default' => null, 'label' => 'Site Key',  'description' => 'Public key (visible to browsers).<br>Test keys:<br>1x00000000000000000000AA (always pass)<br>2x00000000000000000000AB (always fail)<br>3x00000000000000000000FF (force interactive)'],
            ['group' => 'forms', 'key' => 'forms.captcha.turnstile.secret_key',    'type' => 'string',  'default' => null, 'label' => 'Secret Key', 'description' => 'Server-side key (never expose).<br>Test keys:<br>1x0000000000000000000000000000000AA (always pass)<br>2x0000000000000000000000000000000AB (always fail)'],

            // Contact
            ['group' => 'contact', 'key' => 'contact.email',           'type' => 'string', 'default' => null, 'label' => 'Email',           'description' => 'Primary contact email address'],
            ['group' => 'contact', 'key' => 'contact.phone',           'type' => 'string', 'default' => null, 'label' => 'Phone',           'description' => 'Primary contact phone number'],
            ['group' => 'contact', 'key' => 'contact.address',         'type' => 'string', 'default' => null, 'label' => 'Address',         'description' => 'Street address'],
            ['group' => 'contact', 'key' => 'contact.city',            'type' => 'string', 'default' => null, 'label' => 'City',            'description' => 'City name'],
            ['group' => 'contact', 'key' => 'contact.country',         'type' => 'string', 'default' => null, 'label' => 'Country',         'description' => 'Country name'],
            ['group' => 'contact', 'key' => 'contact.postal_code',     'type' => 'string', 'default' => null, 'label' => 'Postal Code',     'description' => 'Postal/ZIP code'],
            ['group' => 'contact', 'key' => 'contact.google_maps_url', 'type' => 'string', 'default' => null, 'label' => 'Google Maps URL', 'description' => 'Google Maps embed or link URL for office location'],
            ['group' => 'contact', 'key' => 'contact.business_hours',  'type' => 'json',   'default' => null, 'label' => 'Business Hours',  'description' => 'JSON object of business hours by day'],
        ];
    }
}
