<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Services\StorageService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StorageServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        Storage::fake('public');
    }

    public function test_store_uploads_file_and_creates_record(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $service = app(StorageService::class);
        $result  = $service->store($file);

        $this->assertInstanceOf(File::class, $result);
        $this->assertSame('photo.jpg', $result->original_filename);
        $this->assertSame('jpg', $result->extension);
        $this->assertSame('image/jpeg', $result->mime_type);
        $this->assertSame('s3', $result->disk);
        $this->assertSame(800, $result->width);
        $this->assertSame(600, $result->height);
        $this->assertNotEmpty($result->hash);

        // Verify file exists in S3
        Storage::disk('s3')->assertExists($result->full_path);
    }

    public function test_store_deduplicates_by_hash(): void
    {
        $file1 = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $service = app(StorageService::class);
        $result1 = $service->store($file1);

        // findByHash should return the existing file
        $result2 = $service->findByHash($result1->hash);

        $this->assertNotNull($result2);
        $this->assertSame($result1->id, $result2->id);
        $this->assertSame($result1->hash, $result2->hash);

        // Only one file record should exist with this hash
        $this->assertSame(1, File::where('hash', $result1->hash)->count());
    }

    public function test_delete_removes_file_and_optimized_versions(): void
    {
        $file = File::factory()->image()->create();

        // Store a fake file in S3
        Storage::disk('s3')->put($file->full_path, 'original-content');

        // Create an attachment with an optimized version
        $attachment = FileAttachment::factory()->optimized()->create(['file_id' => $file->id]);
        Storage::disk('s3')->put($attachment->optimized_path, 'optimized-content');

        $service = app(StorageService::class);
        $result  = $service->delete($file);

        $this->assertTrue($result);
        Storage::disk('s3')->assertMissing($file->full_path);
        Storage::disk('s3')->assertMissing($attachment->optimized_path);
    }

    public function test_find_by_hash_returns_existing_file(): void
    {
        $file = File::factory()->create(['hash' => 'abc123hash']);

        $service = app(StorageService::class);
        $result  = $service->findByHash('abc123hash');

        $this->assertNotNull($result);
        $this->assertSame($file->id, $result->id);
    }

    public function test_find_by_hash_returns_null_for_unknown_hash(): void
    {
        $service = app(StorageService::class);
        $result  = $service->findByHash('nonexistent-hash');

        $this->assertNull($result);
    }

    public function test_response_includes_cache_headers(): void
    {
        $file    = File::factory()->image()->create();
        $content = 'image-content';

        Storage::disk('s3')->put($file->full_path, $content);

        $service = app(StorageService::class);

        $request = Request::create('/storage/' . $file->full_path, 'GET');

        $response = $service->response($request, $file->full_path);

        $this->assertNotNull($response->headers->get('Cache-Control'));
        $this->assertNotNull($response->headers->get('ETag'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=31536000', $response->headers->get('Cache-Control'));
    }

    public function test_response_returns_304_for_matching_etag(): void
    {
        $file    = File::factory()->image()->create();
        $content = 'test-image-content';

        Storage::disk('s3')->put($file->full_path, $content);

        $etag = '"' . md5($content) . '"';

        $service = app(StorageService::class);

        $request = Request::create('/storage/' . $file->full_path, 'GET', [], [], [], [
            'HTTP_IF_NONE_MATCH' => $etag,
        ]);

        $response = $service->response($request, $file->full_path);

        $this->assertSame(304, $response->getStatusCode());
    }
}
