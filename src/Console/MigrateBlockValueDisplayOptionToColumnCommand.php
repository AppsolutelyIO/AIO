<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Models\PageBlockValue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Migrate a display_options key to a top-level column in page_block_values.
 *
 * Usage:
 *   php artisan block-value:migrate-display-option-to-column anchor_label anchor_label
 *   php artisan block-value:migrate-display-option-to-column style view_style
 */
final class MigrateBlockValueDisplayOptionToColumnCommand extends Command
{
    protected $signature = 'block-value:migrate-display-option-to-column
                            {variableInDisplayOptions : Key in display_options JSON (e.g. anchor_label, style)}
                            {columnInTable : Column name in page_block_values table (e.g. anchor_label, view_style)}
                            {--dry-run : Show what would be updated without saving}
                            {--strip-from-display-options : Remove key from display_options after copying to column}
                            {--debug : Show debug info (total block values, skip reasons)}';

    protected $description = 'Copy display_options[key] to a top-level column for all block values (batch migration)';

    public function handle(): int
    {
        $variableInDisplayOptions = $this->argument('variableInDisplayOptions');
        $columnInTable            = $this->argument('columnInTable');
        $dryRun                   = $this->option('dry-run');
        $stripFromDisplayOptions  = $this->option('strip-from-display-options');

        if (! $this->validateColumn($columnInTable)) {
            return self::FAILURE;
        }

        $values  = PageBlockValue::query()->get();
        $updated = 0;
        $debug   = $this->option('debug');

        if ($debug) {
            $this->line('Total PageBlockValues: ' . $values->count());
        }

        foreach ($values as $blockValue) {
            $displayOptions = $blockValue->display_options;

            // Parse if string (e.g. double-encoded from textarea, or raw JSON from DB)
            if (is_string($displayOptions)) {
                $decoded        = json_decode($displayOptions, true);
                $displayOptions = is_array($decoded) ? $decoded : [];
            }

            // Fallback: read raw from DB (bypasses cast; handles edge cases)
            if (! is_array($displayOptions) || empty($displayOptions)) {
                $raw = DB::table('page_block_values')->where('id', $blockValue->id)->value('display_options');
                if (is_string($raw)) {
                    $decoded        = json_decode($raw, true);
                    $displayOptions = is_array($decoded) ? $decoded : [];
                }
            }

            if (! is_array($displayOptions) || ! array_key_exists($variableInDisplayOptions, $displayOptions)) {
                if ($debug && ! empty($displayOptions)) {
                    $this->line('  Skip #' . $blockValue->id . ': no "' . $variableInDisplayOptions . '" in display_options (keys: ' . implode(', ', array_keys($displayOptions)) . ')');
                }

                continue;
            }

            $value              = $displayOptions[$variableInDisplayOptions];
            $currentColumnValue = $blockValue->getAttribute($columnInTable);

            if ((string) $value === (string) $currentColumnValue && ! $stripFromDisplayOptions) {
                continue;
            }

            $this->line(sprintf(
                '  BlockValue #%d: %s -> %s%s',
                $blockValue->id,
                $columnInTable,
                is_scalar($value) ? (string) $value : json_encode($value),
                $stripFromDisplayOptions ? ' (strip from display_options)' : ''
            ));

            if (! $dryRun) {
                $blockValue->setAttribute($columnInTable, $value);

                if ($stripFromDisplayOptions) {
                    unset($displayOptions[$variableInDisplayOptions]);
                    $blockValue->display_options = $displayOptions;
                }

                $blockValue->save();
            }

            $updated++;
        }

        $this->info(sprintf(
            '%s: %d block value(s) %s.',
            $dryRun ? 'Dry run' : 'Done',
            $updated,
            $dryRun ? 'would be updated' : 'updated'
        ));

        return self::SUCCESS;
    }

    private function validateColumn(string $column): bool
    {
        $allowed = ['anchor_label', 'view_style'];

        if (! in_array($column, $allowed, true)) {
            $this->error('Column must be one of: ' . implode(', ', $allowed));

            return false;
        }

        return true;
    }
}
