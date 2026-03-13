<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Application Helper Functions
|--------------------------------------------------------------------------
|
| This file serves as a bootstrap loader for all helper function files.
| Each file is organized by domain for better maintainability:
|
| - app_config_helpers.php    → App prefix, config shortcuts, locale support
| - http_helpers.php          → Client IP detection, API token resolution
| - translation_helpers.php   → __t(), __tv(), __translate(), enum translations
| - logging_helpers.php       → app_log(), log_error/info/debug/warning(), local_debug()
| - url_helpers.php           → URL generation, slug normalization, path joining
| - timezone_helpers.php      → Timezone conversion, user/app timezone utilities
| - theme_helpers.php         → Theme paths, Vite manifest, themed views/assets
| - admin_ui_helpers.php      → Admin buttons, links, row actions, truncate, placeholder
| - grid_helpers.php          → Grid column formatters, value extractors
| - site_config_helpers.php   → basic_config(), site_name/title/logo/favicon/etc.
| - markdown_helpers.php      → Markdown to HTML conversion, image extraction
| - data_helpers.php          → Array utilities, enum options, settings, page meta
|
*/

$helperFiles = [
    'app_config_helpers.php',
    'http_helpers.php',
    'translation_helpers.php',
    'logging_helpers.php',
    'url_helpers.php',
    'timezone_helpers.php',
    'theme_helpers.php',
    'admin_ui_helpers.php',
    'grid_helpers.php',
    'site_config_helpers.php',
    'markdown_helpers.php',
    'data_helpers.php',
];

foreach ($helperFiles as $file) {
    require_once __DIR__ . '/' . $file;
}
