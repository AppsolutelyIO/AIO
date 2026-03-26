<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CleanupOrphanMigrationsCommand extends Command
{
    protected $signature = 'migrate:cleanup-orphans
        {--dry-run : Show orphan records without deleting them}';

    protected $description = 'Remove migration records from the database that have no corresponding migration file';

    public function handle(): int
    {
        $migrationPaths = $this->getMigrationPaths();
        $existingFiles  = $this->getExistingMigrationFiles($migrationPaths);
        $dbRecords      = DB::table('migrations')->get();

        $orphans = $dbRecords->filter(
            fn ($record) => ! isset($existingFiles[$record->migration])
        );

        if ($orphans->isEmpty()) {
            $this->info('No orphan migration records found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Migration', 'Batch'],
            $orphans->map(fn ($r) => [$r->id, $r->migration, $r->batch])->toArray()
        );

        if ($this->option('dry-run')) {
            $this->warn("Found {$orphans->count()} orphan record(s). Run without --dry-run to delete.");

            return self::SUCCESS;
        }

        DB::table('migrations')->whereIn('id', $orphans->pluck('id'))->delete();

        $this->info("Deleted {$orphans->count()} orphan migration record(s).");

        return self::SUCCESS;
    }

    /**
     * Get all migration paths registered in the application.
     *
     * @return array<int, string>
     */
    private function getMigrationPaths(): array
    {
        $migrator = app('migrator');
        $paths    = $migrator->paths();
        $paths[]  = database_path('migrations');

        return $paths;
    }

    /**
     * Scan migration paths and build a lookup of migration name => file path.
     *
     * @param  array<int, string>  $paths
     * @return array<string, string>
     */
    private function getExistingMigrationFiles(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::files($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $name         = $file->getFilenameWithoutExtension();
                $files[$name] = $file->getRealPath();
            }
        }

        return $files;
    }
}
