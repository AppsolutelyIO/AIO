<?php

namespace Appsolutely\AIO\Tests\Unit;

use PHPUnit\Framework\Attributes\Group;

/**
 * Migration regression tests.
 *
 * Ensures the dcat → aio rename is complete and no stale references remain.
 */
#[Group('migration-regression')]
class MigrationRegressionTest extends TestCase
{
    /**
     * Source directories to scan for stale dcat references.
     */
    protected function getSrcFiles(): array
    {
        $files = [];
        $dirs  = [
            __DIR__ . '/../../src',
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Get dist JS files.
     */
    protected function getDistJsFiles(): array
    {
        $files   = [];
        $distDir = __DIR__ . '/../../resources/dist';

        if (! is_dir($distDir)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($distDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'js') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    // --- Namespace tests ---

    public function test_no_dcat_admin_namespace_in_src()
    {
        $violations = [];

        foreach ($this->getSrcFiles() as $file) {
            $content = file_get_contents($file);

            // Look for Dcat\Admin namespace usage (not Dcat\Laravel which is a dependency)
            if (preg_match('/\bDcat\\\\Admin\b/', $content)) {
                $violations[] = str_replace(realpath(__DIR__ . '/../..'), '', $file);
            }
        }

        $this->assertEmpty(
            $violations,
            "Found Dcat\\Admin namespace references in:\n" . implode("\n", $violations)
        );
    }

    // --- CSS class prefix tests ---

    public function test_no_dcat_css_prefix_in_src()
    {
        $violations = [];

        foreach ($this->getSrcFiles() as $file) {
            $content  = file_get_contents($file);
            $basename = basename($file);

            // Skip files that might legitimately reference dcat (e.g., migration notes)
            if (in_array($basename, ['CHANGELOG.md'])) {
                continue;
            }

            // Look for CSS class references like 'dcat-box', 'dcat-grid', etc.
            if (preg_match('/[\'"]dcat-/', $content)) {
                $violations[] = str_replace(realpath(__DIR__ . '/../..'), '', $file);
            }
        }

        $this->assertEmpty(
            $violations,
            "Found 'dcat-' CSS class references in:\n" . implode("\n", $violations)
        );
    }

    // --- JS global object tests ---

    public function test_no_dcat_js_global_in_src()
    {
        $violations = [];

        foreach ($this->getSrcFiles() as $file) {
            $content = file_get_contents($file);

            // Look for Dcat. JS global references (but not DcatPage or other non-renamed items)
            // We check for common patterns like 'Dcat.ready', 'Dcat.init', 'CreateDcat'
            if (preg_match('/\bCreateDcat\b/', $content)
                || preg_match('/\bDcat\.ready\b/', $content)
                || preg_match('/\bDcat\.init\b/', $content)) {
                $violations[] = str_replace(realpath(__DIR__ . '/../..'), '', $file);
            }
        }

        $this->assertEmpty(
            $violations,
            "Found Dcat JS global references in:\n" . implode("\n", $violations)
        );
    }

    // --- Asset alias tests ---

    public function test_no_dcat_admin_asset_alias_in_src()
    {
        $violations = [];

        foreach ($this->getSrcFiles() as $file) {
            $content = file_get_contents($file);

            // Look for @dcat asset alias references (should be @aio or @admin)
            if (preg_match('/@dcat(?![\w])/', $content)) {
                $violations[] = str_replace(realpath(__DIR__ . '/../..'), '', $file);
            }
        }

        $this->assertEmpty(
            $violations,
            "Found @dcat asset alias references in:\n" . implode("\n", $violations)
        );
    }

    // --- Route prefix tests ---

    public function test_no_dcat_api_route_prefix_in_src()
    {
        $violations = [];

        foreach ($this->getSrcFiles() as $file) {
            $content = file_get_contents($file);

            // Look for dcat-api route prefix (should be api)
            if (preg_match('/[\'"]dcat-api/', $content)) {
                $violations[] = str_replace(realpath(__DIR__ . '/../..'), '', $file);
            }
        }

        $this->assertEmpty(
            $violations,
            "Found 'dcat-api' route prefix references in:\n" . implode("\n", $violations)
        );
    }

    // --- Config key tests ---

    public function test_no_dcat_config_keys_in_src()
    {
        $violations = [];

        foreach ($this->getSrcFiles() as $file) {
            $content = file_get_contents($file);

            // Look for config('dcat. or config("dcat.
            if (preg_match("/config\(['\"]dcat\./", $content)) {
                $violations[] = str_replace(realpath(__DIR__ . '/../..'), '', $file);
            }
        }

        $this->assertEmpty(
            $violations,
            "Found config('dcat.') references in:\n" . implode("\n", $violations)
        );
    }

    // --- Dist file tests ---

    public function test_dist_js_no_dcat_global()
    {
        $jsFiles = $this->getDistJsFiles();

        if (empty($jsFiles)) {
            $this->markTestSkipped('No dist JS files found');
        }

        $violations = [];

        foreach ($jsFiles as $file) {
            $content  = file_get_contents($file);
            $relative = str_replace(realpath(__DIR__ . '/../..'), '', $file);

            // Skip source maps
            if (str_ends_with($file, '.map.js')) {
                continue;
            }

            // Check for Dcat global object (but not in comments/sourcemaps)
            // Remove single-line comments and sourceMappingURL lines
            $cleaned = preg_replace('/\/\/.*$/m', '', $content ?? '');

            if (preg_match('/\bDcat\b/', $cleaned ?? '')) {
                $violations[] = $relative;
            }
        }

        $this->assertEmpty(
            $violations,
            "Found 'Dcat' references in dist JS files:\n" . implode("\n", $violations)
        );
    }

    // --- View file tests ---

    public function test_no_dcat_references_in_views()
    {
        $viewDir = __DIR__ . '/../../resources/views';

        if (! is_dir($viewDir)) {
            $this->markTestSkipped('No views directory found');
        }

        $violations = [];
        $iterator   = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $content  = file_get_contents($file->getPathname());
            $relative = str_replace(realpath(__DIR__ . '/../..'), '', $file->getPathname());

            if (preg_match('/\bDcat\b/', $content) || preg_match('/\bdcat-/', $content)) {
                $violations[] = $relative;
            }
        }

        $this->assertEmpty(
            $violations,
            "Found Dcat references in view files:\n" . implode("\n", $violations)
        );
    }

    // --- Translation file tests ---

    public function test_no_dcat_references_in_lang_files()
    {
        $langDir = __DIR__ . '/../../resources/lang';

        if (! is_dir($langDir)) {
            $this->markTestSkipped('No lang directory found');
        }

        $violations = [];
        $iterator   = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($langDir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content  = file_get_contents($file->getPathname());
            $relative = str_replace(realpath(__DIR__ . '/../..'), '', $file->getPathname());

            // Check for dcat-admin or Dcat Admin in translation values
            if (preg_match('/dcat-admin/', $content) || preg_match('/\bDcat Admin\b/', $content)) {
                $violations[] = $relative;
            }
        }

        $this->assertEmpty(
            $violations,
            "Found Dcat references in lang files:\n" . implode("\n", $violations)
        );
    }
}
