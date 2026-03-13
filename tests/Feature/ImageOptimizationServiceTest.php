<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Services\Contracts\ImageOptimizationServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Appsolutely\AIO\Tests\TestCase;

class ImageOptimizationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    public function test_is_optimizable_returns_true_for_jpg(): void
    {
        $file = File::factory()->create(['extension' => 'jpg']);

        $service = app(ImageOptimizationServiceInterface::class);

        $this->assertTrue($service->isOptimizable($file));
    }

    public function test_is_optimizable_returns_true_for_png(): void
    {
        $file = File::factory()->create(['extension' => 'png']);

        $service = app(ImageOptimizationServiceInterface::class);

        $this->assertTrue($service->isOptimizable($file));
    }

    public function test_is_optimizable_returns_false_for_svg(): void
    {
        $file = File::factory()->create(['extension' => 'svg']);

        $service = app(ImageOptimizationServiceInterface::class);

        $this->assertFalse($service->isOptimizable($file));
    }

    public function test_is_optimizable_returns_false_for_pdf(): void
    {
        $file = File::factory()->pdf()->create();

        $service = app(ImageOptimizationServiceInterface::class);

        $this->assertFalse($service->isOptimizable($file));
    }

    public function test_is_optimizable_returns_false_for_webp(): void
    {
        $file = File::factory()->create(['extension' => 'webp']);

        $service = app(ImageOptimizationServiceInterface::class);

        $this->assertFalse($service->isOptimizable($file));
    }

    public function test_optimize_creates_webp_version(): void
    {
        // Create a real JPEG image for testing
        $imageContent = $this->createTestJpegContent(200, 150);

        $file = File::factory()->create([
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'disk'      => 's3',
        ]);

        Storage::disk('s3')->put($file->full_path, $imageContent);

        $service = app(ImageOptimizationServiceInterface::class);
        $result  = $service->optimize($file);

        $this->assertSame('webp', $result['format']);
        $this->assertGreaterThan(0, $result['size']);
        $this->assertGreaterThan(0, $result['width']);
        $this->assertGreaterThan(0, $result['height']);
        $this->assertNotEmpty($result['path']);
        $this->assertStringStartsWith('optimized/', $result['path']);

        // Verify optimized file was stored
        Storage::disk('s3')->assertExists($result['path']);
    }

    public function test_optimize_scales_down_large_images(): void
    {
        $imageContent = $this->createTestJpegContent(3000, 2000);

        $file = File::factory()->create([
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'disk'      => 's3',
        ]);

        Storage::disk('s3')->put($file->full_path, $imageContent);

        $service = app(ImageOptimizationServiceInterface::class);
        $result  = $service->optimize($file, ['maxWidth' => 1920, 'maxHeight' => 1080]);

        // Should be scaled down proportionally
        $this->assertLessThanOrEqual(1920, $result['width']);
        $this->assertLessThanOrEqual(1080, $result['height']);
    }

    public function test_optimize_for_attachment_updates_record(): void
    {
        $imageContent = $this->createTestJpegContent(200, 150);

        $file = File::factory()->create([
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'disk'      => 's3',
        ]);

        Storage::disk('s3')->put($file->full_path, $imageContent);

        $attachment = FileAttachment::factory()->create(['file_id' => $file->id]);

        $service = app(ImageOptimizationServiceInterface::class);
        $result  = $service->optimizeForAttachment($attachment);

        $this->assertTrue($result->hasOptimizedVersion());
        $this->assertSame('webp', $result->optimized_format);
        $this->assertGreaterThan(0, $result->optimized_size);
        $this->assertNotNull($result->optimized_path);
    }

    public function test_optimize_for_attachment_skips_non_optimizable(): void
    {
        $file = File::factory()->pdf()->create();

        $attachment = FileAttachment::factory()->create(['file_id' => $file->id]);

        $service = app(ImageOptimizationServiceInterface::class);
        $result  = $service->optimizeForAttachment($attachment);

        $this->assertFalse($result->hasOptimizedVersion());
    }

    public function test_extract_dimensions_returns_dimensions_for_image(): void
    {
        $imageContent = $this->createTestJpegContent(320, 240);

        Storage::disk('s3')->put('test/image.jpg', $imageContent);

        $service = app(ImageOptimizationServiceInterface::class);
        $result  = $service->extractDimensions('test/image.jpg', 's3');

        $this->assertNotNull($result);
        $this->assertSame(320, $result['width']);
        $this->assertSame(240, $result['height']);
    }

    public function test_extract_dimensions_returns_null_for_missing_file(): void
    {
        $service = app(ImageOptimizationServiceInterface::class);
        $result  = $service->extractDimensions('nonexistent/file.jpg', 's3');

        $this->assertNull($result);
    }

    /**
     * Create a real JPEG image content for testing.
     */
    private function createTestJpegContent(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $color);

        ob_start();
        imagejpeg($image, null, 90);
        $content = ob_get_clean();

        imagedestroy($image);

        return $content;
    }
}
