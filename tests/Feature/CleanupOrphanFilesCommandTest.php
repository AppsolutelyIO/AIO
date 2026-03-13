<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Appsolutely\AIO\Tests\TestCase;

class CleanupOrphanFilesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    public function test_dry_run_lists_orphan_files_without_deleting(): void
    {
        $orphan = File::factory()->create(['created_at' => now()->subDays(31)]);

        $this->artisan('files:cleanup-orphans', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Dry run');

        $this->assertDatabaseHas('files', ['id' => $orphan->id]);
    }

    public function test_skips_files_with_attachments(): void
    {
        $file = File::factory()->create(['created_at' => now()->subDays(31)]);
        FileAttachment::factory()->create(['file_id' => $file->id]);

        $this->artisan('files:cleanup-orphans', ['--dry-run' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('No orphan files found');
    }

    public function test_skips_recent_orphan_files(): void
    {
        File::factory()->create(['created_at' => now()->subDays(5)]);

        $this->artisan('files:cleanup-orphans', ['--dry-run' => true, '--days' => 30])
            ->assertSuccessful()
            ->expectsOutputToContain('No orphan files found');
    }

    public function test_deletes_orphan_files_when_confirmed(): void
    {
        $orphan = File::factory()->create(['created_at' => now()->subDays(31)]);
        Storage::disk('s3')->put($orphan->full_path, 'content');

        $this->artisan('files:cleanup-orphans')
            ->expectsConfirmation('Delete 1 orphan file(s)?', 'yes')
            ->assertSuccessful()
            ->expectsOutputToContain('Deleted 1 orphan file(s)');

        $this->assertDatabaseMissing('files', ['id' => $orphan->id]);
        Storage::disk('s3')->assertMissing($orphan->full_path);
    }

    public function test_aborts_when_not_confirmed(): void
    {
        $orphan = File::factory()->create(['created_at' => now()->subDays(31)]);

        $this->artisan('files:cleanup-orphans')
            ->expectsConfirmation('Delete 1 orphan file(s)?', 'no')
            ->assertSuccessful()
            ->expectsOutputToContain('Aborted');

        $this->assertDatabaseHas('files', ['id' => $orphan->id]);
    }
}
