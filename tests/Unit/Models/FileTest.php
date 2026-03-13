<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Tests\TestCase;

final class FileTest extends TestCase
{
    // --- getFullPathAttribute ---

    public function test_full_path_concatenates_path_and_filename(): void
    {
        $file           = new File();
        $file->path     = 'uploads/images';
        $file->filename = 'photo.jpg';

        $this->assertSame('uploads/images/photo.jpg', $file->full_path);
    }

    public function test_full_path_with_nested_directory(): void
    {
        $file           = new File();
        $file->path     = 'storage/app/public/documents';
        $file->filename = 'report.pdf';

        $this->assertSame('storage/app/public/documents/report.pdf', $file->full_path);
    }

    public function test_full_path_with_empty_path(): void
    {
        $file           = new File();
        $file->path     = '';
        $file->filename = 'file.txt';

        $this->assertSame('/file.txt', $file->full_path);
    }
}
