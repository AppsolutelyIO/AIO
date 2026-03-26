<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Models\PageBlockValue;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Compare DB display_options against manifest displayOptionsDefinition (including
 * nested table/object field keys), and sync missing keys from DB into the manifest.
 *
 * Usage:
 *   php artisan manifest:sync-display-options june
 *   php artisan manifest:sync-display-options june --dry-run
 */
final class SyncDisplayOptionsToManifestCommand extends Command
{
    protected $signature = 'manifest:sync-display-options
                            {theme : Theme name (e.g. june, tabler)}
                            {--dry-run : Show differences without writing to manifest}';

    protected $description = 'Sync display options from DB block values into manifest.json displayOptionsDefinition';

    private int $totalMissing = 0;

    public function handle(ManifestServiceInterface $manifestService): int
    {
        $theme  = $this->argument('theme');
        $dryRun = $this->option('dry-run');

        $manifest = $manifestService->loadManifest($theme);

        if (empty($manifest)) {
            $this->error("No manifest.json found for theme: {$theme}");

            return self::FAILURE;
        }

        $templates = $manifest['templates'] ?? [];

        $dbDataByView = $this->collectDbDisplayOptions($theme);

        if ($dbDataByView->isEmpty()) {
            $this->info("No page block values found for theme: {$theme}");

            return self::SUCCESS;
        }

        $changed = false;

        foreach ($dbDataByView as $view => $dbDisplayOptions) {
            if (! isset($templates[$view])) {
                $this->warn("  View '{$view}' exists in DB but not in manifest — skipped");

                continue;
            }

            $definition    = $templates[$view]['displayOptionsDefinition'] ?? [];
            $defaults      = $templates[$view]['displayOptions'] ?? [];
            $viewHasChange = false;

            $this->diffAndMerge(
                $dbDisplayOptions,
                $definition,
                $defaults,
                $view,
                '',
                $viewHasChange,
            );

            if ($viewHasChange) {
                $manifest['templates'][$view]['displayOptionsDefinition'] = $definition;
                $manifest['templates'][$view]['displayOptions']           = $defaults;
                $changed                                                  = true;
            }
        }

        if ($this->totalMissing === 0) {
            $this->info('Manifest is in sync with DB — no missing keys found.');

            return self::SUCCESS;
        }

        $this->line('');
        $this->info("Total: {$this->totalMissing} missing key(s)");

        if ($dryRun) {
            $this->warn('Dry run — no changes written.');

            return self::SUCCESS;
        }

        if ($changed) {
            $this->writeManifest($theme, $manifest);
            $this->info('Manifest updated successfully.');
            $manifestService->clearCache($theme);
        }

        return self::SUCCESS;
    }

    /**
     * Collect all DB display_options merged by view — union of all keys across all rows.
     *
     * @return Collection<string, array<string, mixed>>
     */
    private function collectDbDisplayOptions(string $theme): Collection
    {
        $values = PageBlockValue::query()
            ->where('theme', $theme)
            ->whereNotNull('display_options')
            ->select('view', 'display_options')
            ->get();

        return $values
            ->groupBy('view')
            ->map(function ($group) {
                $merged = [];
                foreach ($group as $blockValue) {
                    $do = $blockValue->display_options;
                    if (is_array($do)) {
                        $merged = $this->deepMergeUnion($merged, $do);
                    }
                }

                return $merged;
            });
    }

    /**
     * Recursively merge two arrays to collect the union of all keys.
     * For table-type (indexed arrays of objects), merge the sub-keys across all rows.
     */
    private function deepMergeUnion(array $base, array $incoming): array
    {
        foreach ($incoming as $key => $value) {
            if (! array_key_exists($key, $base)) {
                $base[$key] = $value;

                continue;
            }

            $baseVal = $base[$key];

            // Both are indexed arrays of objects (table rows) — merge sub-keys
            if (
                is_array($baseVal) && is_array($value)
                && array_is_list($baseVal) && array_is_list($value)
            ) {
                $baseRow     = $this->collectTableRowKeys($baseVal);
                $incomingRow = $this->collectTableRowKeys($value);
                if (! empty($baseRow) || ! empty($incomingRow)) {
                    $mergedRow = $this->deepMergeUnion($baseRow, $incomingRow);
                    // Store as a single-row array to preserve structure
                    $base[$key] = [$mergedRow];
                }

                continue;
            }

            // Both are associative arrays (object) — recurse
            if (
                is_array($baseVal) && is_array($value)
                && ! array_is_list($baseVal) && ! array_is_list($value)
            ) {
                $base[$key] = $this->deepMergeUnion($baseVal, $value);
            }
        }

        return $base;
    }

    /**
     * Flatten table rows (indexed array of objects) into a single associative array
     * with the union of all keys from all rows.
     */
    private function collectTableRowKeys(array $rows): array
    {
        $merged = [];
        foreach ($rows as $row) {
            if (is_array($row) && ! array_is_list($row)) {
                $merged = $this->deepMergeUnion($merged, $row);
            }
        }

        return $merged;
    }

    /**
     * Recursively compare DB data against manifest definition, reporting and collecting
     * missing keys at every level (top-level, table fields, object fields).
     *
     * @param  array<string, mixed>  $dbData  Merged DB display_options (or sub-level)
     * @param  array<string, mixed>  &$definition  Manifest displayOptionsDefinition (mutated)
     * @param  array<string, mixed>  &$defaults  Manifest displayOptions defaults (mutated)
     * @param  string  $view  Template name (for display)
     * @param  string  $path  Dot-separated path for nested display (e.g. "heroes.image_alt")
     * @param  bool  &$hasChange  Set to true if any change was made
     */
    private function diffAndMerge(
        array $dbData,
        array &$definition,
        array &$defaults,
        string $view,
        string $path,
        bool &$hasChange,
    ): void {
        $defKeys     = array_keys($definition);
        $dbKeys      = array_keys($dbData);
        $missingKeys = array_diff($dbKeys, $defKeys);

        // Report and add missing top-level keys at this level
        if (! empty($missingKeys)) {
            $prefix = $path === '' ? $view : "{$view}.{$path}";
            $this->line('');
            $this->line("  <comment>{$prefix}</comment>: " . count($missingKeys) . ' missing key(s)');

            foreach ($missingKeys as $key) {
                $inferred          = $this->inferSingleDefinition($key, $dbData[$key]);
                $definition[$key]  = $inferred;
                $typeLabel         = $inferred['type'];
                $displayPath       = $path === '' ? $key : "{$path}.{$key}";
                $this->line("    + <info>{$displayPath}</info> (inferred type: {$typeLabel})");
                $this->totalMissing++;
                $hasChange = true;

                if (! array_key_exists($key, $defaults)) {
                    $defaults[$key] = $inferred['default'] ?? '';
                }

                // Recursively print nested fields for newly inferred table/object types
                if (in_array($typeLabel, ['table', 'object'], true) && ! empty($inferred['fields'])) {
                    $this->printNestedFields($inferred['fields'], $displayPath);
                }
            }
        }

        // Recurse into existing keys that are table or object types
        foreach ($definition as $key => &$def) {
            if (! is_array($def) || ! array_key_exists($key, $dbData)) {
                continue;
            }

            $dbValue  = $dbData[$key];
            $defType  = $def['type'] ?? null;
            $nextPath = $path === '' ? $key : "{$path}.{$key}";

            // table type — compare fields sub-keys
            if ($defType === 'table' && is_array($dbValue)) {
                $dbSubKeys = $this->flattenTableRowKeysFromValue($dbValue);
                if (empty($dbSubKeys)) {
                    continue;
                }

                $fields         = $def['fields'] ?? [];
                $defaultsStub   = []; // table fields don't have top-level defaults
                $this->diffAndMerge($dbSubKeys, $fields, $defaultsStub, $view, $nextPath, $hasChange);
                $def['fields'] = $fields;
            }

            // object type — compare fields sub-keys
            if ($defType === 'object' && is_array($dbValue) && ! array_is_list($dbValue)) {
                $fields       = $def['fields'] ?? [];
                $objDefaults  = $defaults[$key] ?? [];
                if (! is_array($objDefaults)) {
                    $objDefaults = [];
                }
                $this->diffAndMerge($dbValue, $fields, $objDefaults, $view, $nextPath, $hasChange);
                $def['fields']  = $fields;
                $defaults[$key] = $objDefaults;
            }
        }
        unset($def);
    }

    /**
     * Recursively print nested fields for newly inferred table/object definitions.
     *
     * @param  array<string, array<string, mixed>>  $fields
     */
    private function printNestedFields(array $fields, string $parentPath): void
    {
        foreach ($fields as $fieldKey => $fieldDef) {
            $fieldType   = $fieldDef['type'] ?? 'text';
            $displayPath = "{$parentPath}.{$fieldKey}";
            $this->line("      <info>{$displayPath}</info> ({$fieldType})");
            $this->totalMissing++;

            if (in_array($fieldType, ['table', 'object'], true) && ! empty($fieldDef['fields'])) {
                $this->printNestedFields($fieldDef['fields'], $displayPath);
            }
        }
    }

    /**
     * Given a DB value that is a table-type (indexed array of objects), collect
     * the union of all row keys as an associative array (key => sample value).
     */
    private function flattenTableRowKeysFromValue(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        // It's a table: indexed array of objects
        if (array_is_list($value)) {
            return $this->collectTableRowKeys($value);
        }

        // Single merged row already
        return $value;
    }

    /**
     * Infer a single displayOptionsDefinition entry from a sample value.
     *
     * @return array<string, mixed>
     */
    private function inferSingleDefinition(string $key, mixed $value): array
    {
        if ($value === null) {
            return $this->makeDefinition('text', $key);
        }

        if (is_bool($value)) {
            return $this->makeDefinition('boolean', $key, $value);
        }

        if (is_int($value) || is_float($value)) {
            return $this->makeDefinition('number', $key, $value);
        }

        if (is_array($value)) {
            // Indexed array of objects → table
            if (array_is_list($value) && ! empty($value) && is_array($value[0])) {
                $mergedRow = $this->collectTableRowKeys($value);
                $fields    = $this->inferFieldsFromRow($mergedRow);

                return [
                    'type'    => 'table',
                    'label'   => $this->keyToLabel($key),
                    'fields'  => $fields,
                    'default' => [],
                ];
            }

            // Associative array → object
            if (! array_is_list($value) && ! empty($value)) {
                $fields = $this->inferFieldsFromRow($value);

                return [
                    'type'   => 'object',
                    'label'  => $this->keyToLabel($key),
                    'fields' => $fields,
                ];
            }

            // Empty or plain array
            return $this->makeDefinition('text', $key, '');
        }

        if (is_string($value)) {
            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
                return $this->makeDefinition('url', $key, $value);
            }

            if (strlen($value) > 100) {
                return $this->makeDefinition('textarea', $key, $value);
            }

            return $this->makeDefinition('text', $key, $value);
        }

        return $this->makeDefinition('text', $key);
    }

    /**
     * Infer field definitions from a sample row (for table/object fields).
     *
     * @return array<string, array<string, mixed>>
     */
    private function inferFieldsFromRow(array $row): array
    {
        $fields = [];
        foreach ($row as $fieldKey => $fieldValue) {
            if (is_array($fieldValue)) {
                // Nested table
                if (array_is_list($fieldValue) && ! empty($fieldValue) && is_array($fieldValue[0])) {
                    $subRow            = $this->collectTableRowKeys($fieldValue);
                    $fields[$fieldKey] = [
                        'type'   => 'table',
                        'label'  => $this->keyToLabel($fieldKey),
                        'fields' => $this->inferFieldsFromRow($subRow),
                    ];
                } elseif (! array_is_list($fieldValue) && ! empty($fieldValue)) {
                    // Nested object
                    $fields[$fieldKey] = [
                        'type'   => 'object',
                        'label'  => $this->keyToLabel($fieldKey),
                        'fields' => $this->inferFieldsFromRow($fieldValue),
                    ];
                } else {
                    $fields[$fieldKey] = [
                        'type'  => 'text',
                        'label' => $this->keyToLabel($fieldKey),
                    ];
                }

                continue;
            }

            $type = match (true) {
                is_bool($fieldValue) => 'boolean',
                is_int($fieldValue), is_float($fieldValue) => 'number',
                is_string($fieldValue) && $this->looksLikeImage($fieldValue) => 'image',
                is_string($fieldValue) && $this->looksLikeUrl($fieldValue)   => 'url',
                default                                                      => 'text',
            };

            $fields[$fieldKey] = [
                'type'  => $type,
                'label' => $this->keyToLabel($fieldKey),
            ];
        }

        return $fields;
    }

    private function looksLikeUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/');
    }

    private function looksLikeImage(string $value): bool
    {
        return (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|svg|avif)(\?|$)/i', $value);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeDefinition(string $type, string $key, mixed $default = null): array
    {
        $def = [
            'type'  => $type,
            'label' => $this->keyToLabel($key),
        ];

        $def['default'] = match ($type) {
            'boolean' => $default ?? false,
            'number'  => $default ?? 0,
            default   => $default ?? '',
        };

        return $def;
    }

    private function keyToLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    private function writeManifest(string $theme, array $manifest): void
    {
        $manifestPath = $this->resolveManifestPath($theme);
        $json         = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        file_put_contents($manifestPath, $json . "\n");
    }

    private function resolveManifestPath(string $theme): string
    {
        $sitePath = base_path("themes/{$theme}/manifest.json");

        if (file_exists($sitePath)) {
            return $sitePath;
        }

        $packagePath = dirname(__DIR__, 2) . '/themes/' . $theme . '/manifest.json';

        if (file_exists($packagePath)) {
            return $packagePath;
        }

        return $sitePath;
    }
}
