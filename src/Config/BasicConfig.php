<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Config;

/**
 * Type-safe configuration accessor for basic application settings
 *
 * This class provides typed access to all basic configuration values
 * stored in the site_settings system. All methods return properly typed
 * values with null safety where appropriate.
 *
 * Usage:
 *   $config = new BasicConfig();
 *   $value = $config->methodName(); // Returns string|null
 *
 * Or use the static helper:
 *   BasicConfig::getMethodName();
 */
final readonly class BasicConfig
{
    /**
     * Get the Name
     */
    public function name(): ?string
    {
        return config('general.site_name');
    }

    /**
     * Get the Title
     */
    public function title(): ?string
    {
        return config('general.site_title');
    }

    /**
     * Get the Keywords
     */
    public function keywords(): ?string
    {
        return config('seo.meta_keywords');
    }

    /**
     * Get the Description
     */
    public function description(): ?string
    {
        return config('seo.meta_description');
    }

    /**
     * Get the Logo for light backgrounds
     */
    public function logoLight(): ?string
    {
        return config('appearance.logo_light');
    }

    /**
     * Get the Logo for dark backgrounds
     */
    public function logoDark(): ?string
    {
        return config('appearance.logo_dark');
    }

    /**
     * Get the Favicon
     */
    public function favicon(): ?string
    {
        return config('appearance.favicon');
    }

    /**
     * Get the Theme
     */
    public function theme(): ?string
    {
        return config('appearance.theme');
    }

    /**
     * Get the Timezone
     */
    public function timezone(): ?string
    {
        return config('general.timezone');
    }

    /**
     * Get the Date Format
     */
    public function dateFormat(): ?string
    {
        return config('general.date_format');
    }

    /**
     * Get the Time Format
     */
    public function timeFormat(): ?string
    {
        return config('general.time_format');
    }

    /**
     * Get the Locale
     */
    public function locale(): ?string
    {
        return config('general.locale');
    }

    /**
     * Get the Site Meta
     */
    public function siteMeta(): ?string
    {
        return config('seo.site_meta');
    }

    /**
     * Get the Structured Data
     */
    public function structuredData(): ?string
    {
        return config('seo.structured_data');
    }

    /**
     * Get the Tracking Code
     */
    public function trackingCode(): ?string
    {
        return config('seo.tracking_code');
    }

    /**
     * Get the Copyright
     */
    public function copyright(): ?string
    {
        return config('general.copyright');
    }

    /**
     * Get the Logo Pattern
     *
     * %s: file extension
     */
    public function logoPattern(): ?string
    {
        return config('appearance.logo_pattern');
    }

    /**
     * Get the Favicon Pattern
     *
     * %s: file extension
     */
    public function faviconPattern(): ?string
    {
        return config('appearance.favicon_pattern');
    }

    /**
     * Get the Noscript
     */
    public function noscript(): ?string
    {
        return config('seo.noscript');
    }

    /**
     * Get the Tagline
     */
    public function tagline(): ?string
    {
        return config('general.tagline');
    }

    /**
     * Get the OG Image
     */
    public function ogImage(): ?string
    {
        return config('seo.og_image');
    }

    /**
     * Get the Robots meta
     */
    public function robots(): ?string
    {
        return config('seo.robots');
    }

    /**
     * Get the Google Site Verification
     */
    public function googleSiteVerification(): ?string
    {
        return config('seo.google_site_verification');
    }

    /**
     * Get the Primary Color
     */
    public function primaryColor(): ?string
    {
        return config('appearance.primary_color');
    }

    /**
     * Get the Secondary Color
     */
    public function secondaryColor(): ?string
    {
        return config('appearance.secondary_color');
    }

    /**
     * Get the Custom CSS
     */
    public function customCss(): ?string
    {
        return config('appearance.custom_css');
    }

    /**
     * Get the Contact Email
     */
    public function contactEmail(): ?string
    {
        return config('contact.email');
    }

    /**
     * Get the Contact Phone
     */
    public function contactPhone(): ?string
    {
        return config('contact.phone');
    }

    /**
     * Get the Contact Address
     */
    public function contactAddress(): ?string
    {
        return config('contact.address');
    }

    /**
     * Get the Contact City
     */
    public function contactCity(): ?string
    {
        return config('contact.city');
    }

    /**
     * Get the Contact Country
     */
    public function contactCountry(): ?string
    {
        return config('contact.country');
    }

    /**
     * Get the Contact Postal Code
     */
    public function contactPostalCode(): ?string
    {
        return config('contact.postal_code');
    }

    /**
     * Get the Google Maps URL
     */
    public function googleMapsUrl(): ?string
    {
        return config('contact.google_maps_url');
    }

    /**
     * Get the Business Hours
     *
     * @return array<string, mixed>|null
     */
    public function businessHours(): ?array
    {
        $value = config('contact.business_hours');

        if (is_string($value)) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * Get a social media URL by platform
     */
    public function social(string $platform): ?string
    {
        return config("social.{$platform}");
    }

    /**
     * Get all social media links as an associative array
     *
     * @return array<string, string|null>
     */
    public function socialLinks(): array
    {
        $platforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'whatsapp'];

        return array_filter(
            array_combine($platforms, array_map(fn (string $p) => config("social.{$p}"), $platforms))
        );
    }

    // Static helper methods for convenience

    /**
     * Get the Name (static)
     */
    public static function getName(): ?string
    {
        return (new self())->name();
    }

    /**
     * Get the Title (static)
     */
    public static function getTitle(): ?string
    {
        return (new self())->title();
    }

    /**
     * Get the Keywords (static)
     */
    public static function getKeywords(): ?string
    {
        return (new self())->keywords();
    }

    /**
     * Get the Description (static)
     */
    public static function getDescription(): ?string
    {
        return (new self())->description();
    }

    /**
     * Get the Logo for light backgrounds (static)
     */
    public static function getLogoLight(): ?string
    {
        return (new self())->logoLight();
    }

    /**
     * Get the Logo for dark backgrounds (static)
     */
    public static function getLogoDark(): ?string
    {
        return (new self())->logoDark();
    }

    /**
     * Get the Favicon (static)
     */
    public static function getFavicon(): ?string
    {
        return (new self())->favicon();
    }

    /**
     * Get the Theme (static)
     */
    public static function getTheme(): ?string
    {
        return (new self())->theme();
    }

    /**
     * Get the Timezone (static)
     */
    public static function getTimezone(): ?string
    {
        return (new self())->timezone();
    }

    /**
     * Get the Date Format (static)
     */
    public static function getDateFormat(): ?string
    {
        return (new self())->dateFormat();
    }

    /**
     * Get the Time Format (static)
     */
    public static function getTimeFormat(): ?string
    {
        return (new self())->timeFormat();
    }

    /**
     * Get the Locale (static)
     */
    public static function getLocale(): ?string
    {
        return (new self())->locale();
    }

    /**
     * Get the Site Meta (static)
     */
    public static function getSiteMeta(): ?string
    {
        return (new self())->siteMeta();
    }

    /**
     * Get the Structured Data (static)
     */
    public static function getStructuredData(): ?string
    {
        return (new self())->structuredData();
    }

    /**
     * Get the Tracking Code (static)
     */
    public static function getTrackingCode(): ?string
    {
        return (new self())->trackingCode();
    }

    /**
     * Get the Copyright (static)
     */
    public static function getCopyright(): ?string
    {
        return (new self())->copyright();
    }

    /**
     * Get the Logo Pattern (static)
     */
    public static function getLogoPattern(): ?string
    {
        return (new self())->logoPattern();
    }

    /**
     * Get the Favicon Pattern (static)
     */
    public static function getFaviconPattern(): ?string
    {
        return (new self())->faviconPattern();
    }

    /**
     * Get the Noscript (static)
     */
    public static function getNoscript(): ?string
    {
        return (new self())->noscript();
    }

    /**
     * Get the Tagline (static)
     */
    public static function getTagline(): ?string
    {
        return (new self())->tagline();
    }

    /**
     * Get the OG Image (static)
     */
    public static function getOgImage(): ?string
    {
        return (new self())->ogImage();
    }

    /**
     * Get the Robots meta (static)
     */
    public static function getRobots(): ?string
    {
        return (new self())->robots();
    }

    /**
     * Get the Google Site Verification (static)
     */
    public static function getGoogleSiteVerification(): ?string
    {
        return (new self())->googleSiteVerification();
    }

    /**
     * Get the Primary Color (static)
     */
    public static function getPrimaryColor(): ?string
    {
        return (new self())->primaryColor();
    }

    /**
     * Get the Secondary Color (static)
     */
    public static function getSecondaryColor(): ?string
    {
        return (new self())->secondaryColor();
    }

    /**
     * Get the Custom CSS (static)
     */
    public static function getCustomCss(): ?string
    {
        return (new self())->customCss();
    }

    /**
     * Get the Contact Email (static)
     */
    public static function getContactEmail(): ?string
    {
        return (new self())->contactEmail();
    }

    /**
     * Get the Contact Phone (static)
     */
    public static function getContactPhone(): ?string
    {
        return (new self())->contactPhone();
    }

    /**
     * Get the Contact Address (static)
     */
    public static function getContactAddress(): ?string
    {
        return (new self())->contactAddress();
    }

    /**
     * Get the Contact City (static)
     */
    public static function getContactCity(): ?string
    {
        return (new self())->contactCity();
    }

    /**
     * Get the Contact Country (static)
     */
    public static function getContactCountry(): ?string
    {
        return (new self())->contactCountry();
    }

    /**
     * Get the Contact Postal Code (static)
     */
    public static function getContactPostalCode(): ?string
    {
        return (new self())->contactPostalCode();
    }

    /**
     * Get the Google Maps URL (static)
     */
    public static function getGoogleMapsUrl(): ?string
    {
        return (new self())->googleMapsUrl();
    }

    /**
     * Get the Business Hours (static)
     *
     * @return array<string, mixed>|null
     */
    public static function getBusinessHours(): ?array
    {
        return (new self())->businessHours();
    }

    /**
     * Get a social media URL by platform (static)
     */
    public static function getSocial(string $platform): ?string
    {
        return (new self())->social($platform);
    }

    /**
     * Get all social media links (static)
     *
     * @return array<string, string|null>
     */
    public static function getSocialLinks(): array
    {
        return (new self())->socialLinks();
    }
}
