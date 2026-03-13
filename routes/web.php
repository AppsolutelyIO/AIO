<?php

declare(strict_types=1);

/**
 * Main web routes file
 *
 * Routes are organized by feature/domain:
 * - web/sitemap.php - Sitemap routes
 * - web/files.php - File and asset routes
 * - web/dashboard.php - Authenticated user routes
 * - auth.php - Authentication routes
 * - fallback.php - Page routes (catch-all, loaded last)
 */

// Load feature-based route files
require __DIR__ . '/web/sitemap.php';
require __DIR__ . '/web/files.php';
require __DIR__ . '/web/dashboard.php';

// Load authentication routes
require __DIR__ . '/auth.php';
