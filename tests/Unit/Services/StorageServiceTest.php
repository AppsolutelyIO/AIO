<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Repositories\AdminSettingRepository;
use Appsolutely\AIO\Repositories\FileRepository;
use Appsolutely\AIO\Services\StorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Appsolutely\AIO\Tests\TestCase;

class StorageServiceTest extends TestCase
{
    use RefreshDatabase;

    private FileRepository $fileRepository;

    private AdminSettingRepository $adminSettingRepository;

    private StorageService $storageService;

    protected function setUp(): void
    {
        parent::setUp();

        // Use real repositories (integration testing approach for final classes)
        $this->fileRepository         = app(FileRepository::class);
        $this->adminSettingRepository = app(AdminSettingRepository::class);
        $this->storageService         = app(StorageService::class);
    }

    public function test_store_file_successfully()
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $storedFile = $this->storageService->store($file);

        $this->assertInstanceOf(File::class, $storedFile);
        Storage::disk('s3')->assertExists($storedFile->path . '/' . $storedFile->filename);
    }

    public function test_store_file_upload_fails()
    {
        Storage::fake('s3');

        // Mock putFileAs to return false (upload failure)
        Storage::shouldReceive('disk->putFileAs')->andReturn(false);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $this->expectException(\App\Exceptions\StorageException::class);
        $this->expectExceptionMessage('Failed to upload file');

        $this->storageService->store($file);
    }

    public function test_store_file_not_found_after_upload()
    {
        Storage::fake('s3');

        // Mock the exists check to return false after putFileAs
        Storage::shouldReceive('disk->putFileAs')->andReturn('2025/11/test.pdf');
        Storage::shouldReceive('disk->exists')->andReturn(false);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $this->expectException(\App\Exceptions\StorageException::class);
        $this->expectExceptionMessage('was not found in S3 storage after upload');

        $this->storageService->store($file);
    }

    public function test_delete_file_successfully()
    {
        Storage::fake('s3');

        $file       = UploadedFile::fake()->create('document.pdf', 100);
        $storedFile = $this->storageService->store($file);
        $fileId     = $storedFile->id;

        $result = $this->storageService->delete($storedFile);

        $this->assertTrue($result);
        // File model uses SoftDeletes, so check for soft deletion
        $this->assertSoftDeleted('files', ['id' => $fileId]);
    }

    public function test_delete_file_fails()
    {
        // Don't use Storage::fake() here - we want to mock the delete to return false
        Storage::shouldReceive('disk')->with('s3')->andReturnSelf();
        Storage::shouldReceive('delete')->andReturn(false);

        $file = new File([
            'path'              => '2023/03',
            'filename'          => 'non-existent.pdf',
            'original_filename' => 'test.pdf',
            'extension'         => 'pdf',
            'mime_type'         => 'application/pdf',
            'size'              => 100,
            'hash'              => 'test-hash',
        ]);
        $file->save();

        // Storage deletion fails, so delete should return false
        $result = $this->storageService->delete($file);

        $this->assertFalse($result);
        // File should not be soft deleted since storage deletion failed
        $this->assertDatabaseHas('files', ['id' => $file->id, 'deleted_at' => null]);
    }

    public function test_get_signed_url()
    {
        Storage::fake('s3');

        $file       = UploadedFile::fake()->create('document.pdf', 100);
        $storedFile = $this->storageService->store($file);

        $url = $this->storageService->getSignedUrl($storedFile);

        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }
}
