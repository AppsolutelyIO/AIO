<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Console;

use Appsolutely\AIO\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:cleanup-orphans
        {--dry-run : Show orphan files without deleting them}
        {--days=30 : Only clean files older than this many days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove orphan files that have no file attachment references';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $days   = (int) $this->option('days');

        $orphans = File::query()
            ->leftJoin('file_attachments', 'files.id', '=', 'file_attachments.file_id')
            ->whereNull('file_attachments.id')
            ->where('files.created_at', '<', now()->subDays($days))
            ->select('files.*')
            ->get();

        if ($orphans->isEmpty()) {
            $this->info('No orphan files found.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d orphan file(s) older than %d days.', $orphans->count(), $days));

        if ($dryRun) {
            $this->table(
                ['ID', 'Filename', 'Path', 'Size', 'Created At'],
                $orphans->map(fn (File $file) => [
                    $file->id,
                    $file->original_filename,
                    $file->full_path,
                    number_format($file->size) . ' bytes',
                    $file->created_at?->toDateTimeString(),
                ])->toArray()
            );
            $this->warn('Dry run — no files were deleted.');

            return self::SUCCESS;
        }

        if (! $this->confirm(sprintf('Delete %d orphan file(s)?', $orphans->count()))) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $failed  = 0;

        foreach ($orphans as $file) {
            $disk = $file->disk ?? 's3';

            try {
                Storage::disk($disk)->delete($file->full_path);
                $file->forceDelete();
                $deleted++;
            } catch (\Exception $e) {
                $this->error(sprintf('Failed to delete file #%d (%s): %s', $file->id, $file->full_path, $e->getMessage()));
                $failed++;
            }
        }

        $this->info(sprintf('Deleted %d orphan file(s). Failed: %d.', $deleted, $failed));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
