<?php

declare(strict_types=1);

use Appsolutely\AIO\Config\BasicConfig;

if (! function_exists('basic_config')) {
    /**
     * Get a basic configuration value by key
     *
     * @param  string  $key  The configuration key (e.g., 'title', 'favicon', 'theme')
     * @return mixed The configuration value
     *
     * @throws InvalidArgumentException If the key does not correspond to a valid method
     */
    function basic_config(string $key): mixed
    {
        $config = new BasicConfig();

        if (! method_exists($config, $key)) {
            // Get available methods automatically using reflection
            $reflection = new ReflectionClass($config);
            $methods    = array_filter(
                array_map(
                    fn (ReflectionMethod $method) => $method->getName(),
                    $reflection->getMethods(ReflectionMethod::IS_PUBLIC)
                ),
                fn (string $methodName) => ! str_starts_with($methodName, 'get') && $methodName !== '__construct'
            );

            throw new InvalidArgumentException(
                "Basic config key '{$key}' does not exist. Available keys: " . implode(', ', $methods)
            );
        }

        return $config->$key();
    }
}

if (! function_exists('site_name')) {
    /**
     * Get the site name
     */
    function site_name(): ?string
    {
        return basic_config('name');
    }
}

if (! function_exists('site_title')) {
    /**
     * Get the site title
     */
    function site_title(): ?string
    {
        return basic_config('title');
    }
}

if (! function_exists('site_keywords')) {
    /**
     * Get the site keywords
     */
    function site_keywords(): ?string
    {
        return basic_config('keywords');
    }
}

if (! function_exists('site_description')) {
    /**
     * Get the site description
     */
    function site_description(): ?string
    {
        return basic_config('description');
    }
}

if (! function_exists('site_logo')) {
    /**
     * Get the appropriate site logo based on context.
     *
     * For dark themes, returns logo_dark (light-colored logo);
     * for light themes, returns logo_light (dark-colored logo).
     * Falls back to whichever variant is available.
     *
     * @param  'light'|'dark'|null  $variant  Force a specific variant, or null to auto-detect from theme
     */
    function site_logo(?string $variant = null): ?string
    {
        if ($variant === 'light') {
            return basic_config('logoLight');
        }

        if ($variant === 'dark') {
            return basic_config('logoDark');
        }

        // Auto-detect: dark theme needs the dark variant (light-colored logo)
        $light = basic_config('logoLight');
        $dark  = basic_config('logoDark');

        return $dark ?? $light;
    }
}

if (! function_exists('site_logo_light')) {
    /**
     * Get the site logo for light backgrounds
     */
    function site_logo_light(): ?string
    {
        return basic_config('logoLight');
    }
}

if (! function_exists('site_logo_dark')) {
    /**
     * Get the site logo for dark backgrounds
     */
    function site_logo_dark(): ?string
    {
        return basic_config('logoDark');
    }
}

if (! function_exists('site_favicon')) {
    /**
     * Get the site favicon path
     */
    function site_favicon(): ?string
    {
        return basic_config('favicon');
    }
}

if (! function_exists('site_theme')) {
    /**
     * Get the site theme name
     */
    function site_theme(): ?string
    {
        return basic_config('theme');
    }
}

if (! function_exists('site_timezone')) {
    /**
     * Get the site timezone
     */
    function site_timezone(): ?string
    {
        return basic_config('timezone');
    }
}

if (! function_exists('site_locale')) {
    /**
     * Get the site locale
     */
    function site_locale(): ?string
    {
        return basic_config('locale');
    }
}

if (! function_exists('site_copyright')) {
    /**
     * Get the site copyright text
     */
    function site_copyright(): ?string
    {
        return basic_config('copyright');
    }
}

if (! function_exists('site_meta')) {
    function site_meta(): string
    {
        return basic_config('siteMeta') ?? '';
    }
}

if (! function_exists('noscript')) {
    function noscript(): string
    {
        return basic_config('noscript') ?? '';
    }
}

if (! function_exists('structured_data')) {
    function structured_data(): string
    {
        return basic_config('structuredData') ?? '';
    }
}

if (! function_exists('tracking_code')) {
    function tracking_code(): string
    {
        return basic_config('trackingCode') ?? '';
    }
}

if (! function_exists('site_tagline')) {
    /**
     * Get the site tagline
     */
    function site_tagline(): ?string
    {
        return basic_config('tagline');
    }
}

if (! function_exists('site_og_image')) {
    /**
     * Get the default Open Graph image
     */
    function site_og_image(): ?string
    {
        return basic_config('ogImage');
    }
}

if (! function_exists('site_robots')) {
    /**
     * Get the robots meta value
     */
    function site_robots(): ?string
    {
        return basic_config('robots');
    }
}

if (! function_exists('site_primary_color')) {
    /**
     * Get the primary brand color
     */
    function site_primary_color(): ?string
    {
        return basic_config('primaryColor');
    }
}

if (! function_exists('site_secondary_color')) {
    /**
     * Get the secondary brand color
     */
    function site_secondary_color(): ?string
    {
        return basic_config('secondaryColor');
    }
}

if (! function_exists('site_custom_css')) {
    /**
     * Get custom CSS
     */
    function site_custom_css(): string
    {
        return basic_config('customCss') ?? '';
    }
}

if (! function_exists('contact_email')) {
    /**
     * Get the contact email
     */
    function contact_email(): ?string
    {
        return basic_config('contactEmail');
    }
}

if (! function_exists('contact_phone')) {
    /**
     * Get the contact phone
     */
    function contact_phone(): ?string
    {
        return basic_config('contactPhone');
    }
}

if (! function_exists('contact_address')) {
    /**
     * Get the contact address
     */
    function contact_address(): ?string
    {
        return basic_config('contactAddress');
    }
}

if (! function_exists('social_links')) {
    /**
     * Get all social media links (non-empty only)
     *
     * @return array<string, string>
     */
    function social_links(): array
    {
        return BasicConfig::getSocialLinks();
    }
}

if (! function_exists('social_link')) {
    /**
     * Get a social media link by platform name
     */
    function social_link(string $platform): ?string
    {
        return BasicConfig::getSocial($platform);
    }
}
