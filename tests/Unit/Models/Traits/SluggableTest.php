<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models\Traits;

use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class SluggableTest extends TestCase
{
    use RefreshDatabase;

    // --- deleting hook uses saveQuietly ---

    public function test_deleting_appends_random_suffix_to_slug(): void
    {
        $product = Product::factory()->create(['slug' => 'test-product']);

        $product->delete();

        $this->assertStringStartsWith('test-product-', $product->fresh()?->slug ?? $product->slug);
    }

    public function test_sluggable_uses_save_quietly_in_deleting_hook(): void
    {
        // Verify that saveQuietly is used (not save) by checking
        // the trait source directly - this prevents unwanted side effects
        // from other model event listeners during soft-delete slug modification
        $reflection = new \ReflectionMethod(Product::class, 'bootSluggable');
        $fileName   = $reflection->getFileName();
        $startLine  = $reflection->getStartLine();
        $endLine    = $reflection->getEndLine();

        $source = implode('', array_slice(file($fileName), $startLine - 1, $endLine - $startLine + 1));

        $this->assertStringContainsString('saveQuietly', $source);
        $this->assertStringNotContainsString('$model->save()', $source);
    }
}
