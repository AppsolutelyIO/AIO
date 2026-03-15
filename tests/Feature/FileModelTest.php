<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FileModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_full_path_attribute(): void
    {
        $file = File::factory()->create([
            'path'     => '2026/03',
            'filename' => 'test-uuid.jpg',
        ]);

        $this->assertSame('2026/03/test-uuid.jpg', $file->full_path);
    }

    public function test_file_is_image_returns_true_for_image_extensions(): void
    {
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];

        foreach ($extensions as $ext) {
            $file = File::factory()->create(['extension' => $ext]);
            $this->assertTrue($file->isImage(), "Expected {$ext} to be recognized as image");
        }
    }

    public function test_file_is_image_returns_false_for_non_image(): void
    {
        $extensions = ['pdf', 'txt', 'doc', 'zip'];

        foreach ($extensions as $ext) {
            $file = File::factory()->create(['extension' => $ext]);
            $this->assertFalse($file->isImage(), "Expected {$ext} not to be recognized as image");
        }
    }

    public function test_file_has_many_attachments(): void
    {
        $file = File::factory()->create();

        FileAttachment::factory()->count(3)->create(['file_id' => $file->id]);

        $this->assertCount(3, $file->attachments);
    }

    public function test_file_casts_metadata_to_array(): void
    {
        $file = File::factory()->create([
            'metadata' => ['exif' => ['camera' => 'Canon'], 'dpi' => 300],
        ]);

        $this->assertIsArray($file->metadata);
        $this->assertSame('Canon', $file->metadata['exif']['camera']);
    }

    public function test_file_casts_dimensions_to_integer(): void
    {
        $file = File::factory()->image()->create();

        $this->assertIsInt($file->width);
        $this->assertIsInt($file->height);
    }

    public function test_attachment_has_optimized_version(): void
    {
        $attachment = FileAttachment::factory()->optimized()->create();

        $this->assertTrue($attachment->hasOptimizedVersion());
    }

    public function test_attachment_without_optimized_version(): void
    {
        $attachment = FileAttachment::factory()->create();

        $this->assertFalse($attachment->hasOptimizedVersion());
    }

    public function test_attachment_serving_path_returns_optimized_when_available(): void
    {
        $file       = File::factory()->image()->create();
        $attachment = FileAttachment::factory()->optimized()->create(['file_id' => $file->id]);

        $this->assertSame($attachment->optimized_path, $attachment->serving_path);
    }

    public function test_attachment_serving_path_falls_back_to_original(): void
    {
        $file       = File::factory()->image()->create();
        $attachment = FileAttachment::factory()->create(['file_id' => $file->id]);

        $this->assertSame($file->full_path, $attachment->serving_path);
    }

    public function test_attachment_casts_sort_order_to_integer(): void
    {
        $attachment = FileAttachment::factory()->create(['sort_order' => 5]);

        $this->assertIsInt($attachment->sort_order);
        $this->assertSame(5, $attachment->sort_order);
    }
}
