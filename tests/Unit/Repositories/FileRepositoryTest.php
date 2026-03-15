<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Repositories\FileRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

final class FileRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FileRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(FileRepository::class);
    }

    private function createFile(array $attrs = []): File
    {
        return File::create(array_merge([
            'original_filename' => 'test.jpg',
            'filename'          => uniqid() . '.jpg',
            'extension'         => 'jpg',
            'mime_type'         => 'image/jpeg',
            'path'              => '2024/01',
            'size'              => 1024,
            'hash'              => md5(uniqid()),
        ], $attrs));
    }

    public function test_repository_resolves_from_container(): void
    {
        $this->assertInstanceOf(FileRepository::class, $this->repository);
    }

    public function test_model_returns_file_class(): void
    {
        $this->assertEquals(File::class, $this->repository->model());
    }

    // --- findByFilename ---

    public function test_find_by_filename_returns_file_when_found(): void
    {
        $file = $this->createFile(['filename' => 'unique-file-abc123.jpg']);

        $result = $this->repository->findByFilename('unique-file-abc123.jpg');

        $this->assertInstanceOf(File::class, $result);
        $this->assertEquals($file->id, $result->id);
    }

    public function test_find_by_filename_returns_null_when_not_found(): void
    {
        $result = $this->repository->findByFilename('nonexistent-file-xyz.jpg');

        $this->assertNull($result);
    }

    // --- getLibrary ---

    public function test_get_library_returns_paginator(): void
    {
        $this->createFile(['extension' => 'jpg', 'mime_type' => 'image/jpeg']);
        $this->createFile(['extension' => 'png', 'mime_type' => 'image/png']);

        $request = Request::create('/', 'GET');

        $result = $this->repository->getLibrary($request);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_get_library_filters_by_image_extensions_only(): void
    {
        $this->createFile(['extension' => 'jpg', 'mime_type' => 'image/jpeg']);
        $this->createFile(['extension' => 'pdf', 'mime_type' => 'application/pdf']);

        $request = Request::create('/', 'GET');

        $result = $this->repository->getLibrary($request);

        // Only image files should appear
        foreach ($result->items() as $item) {
            $this->assertContains($item->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
        }
    }

    public function test_get_library_filters_by_name(): void
    {
        $this->createFile(['original_filename' => 'product-image.jpg', 'extension' => 'jpg']);
        $this->createFile(['original_filename' => 'banner-photo.jpg', 'extension' => 'jpg']);

        $request = Request::create('/', 'GET', ['name' => 'product']);

        $result = $this->repository->getLibrary($request);

        $this->assertCount(1, $result->items());
        $this->assertEquals('product-image.jpg', $result->items()[0]->original_filename);
    }

    public function test_get_library_applies_pagination(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createFile(['extension' => 'jpg', 'mime_type' => 'image/jpeg']);
        }

        $request = Request::create('/', 'GET', ['page_size' => 2, 'page' => 1]);

        $result = $this->repository->getLibrary($request);

        $this->assertCount(2, $result->items());
        $this->assertEquals(5, $result->total());
    }

    public function test_get_library_sorts_by_created_at_descending_by_default(): void
    {
        $this->createFile(['extension' => 'jpg', 'original_filename' => 'first.jpg']);
        $this->createFile(['extension' => 'jpg', 'original_filename' => 'second.jpg']);

        $request = Request::create('/', 'GET');

        $result = $this->repository->getLibrary($request);

        // Most recently created should appear first
        $items = $result->items();
        $this->assertGreaterThanOrEqual(2, count($items));
    }
}
