<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\ReviewStatus;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_review_belongs_to_product(): void
    {
        $review = ProductReview::factory()->create();

        $this->assertInstanceOf(Product::class, $review->product);
    }

    public function test_product_review_belongs_to_user(): void
    {
        $review = ProductReview::factory()->create();

        $this->assertInstanceOf(User::class, $review->user);
    }

    public function test_product_review_default_status_is_pending(): void
    {
        $review = ProductReview::factory()->create();

        $this->assertEquals(ReviewStatus::Pending, $review->status);
    }

    public function test_product_review_approved_state(): void
    {
        $review = ProductReview::factory()->approved()->create();

        $this->assertEquals(ReviewStatus::Approved, $review->status);
    }

    public function test_product_review_is_verified(): void
    {
        $review = ProductReview::factory()->verified()->create();

        $this->assertTrue($review->isVerified());
    }

    public function test_product_review_is_not_verified(): void
    {
        $review = ProductReview::factory()->create();

        $this->assertFalse($review->isVerified());
    }

    public function test_product_has_many_reviews(): void
    {
        $product = Product::factory()->create();
        ProductReview::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->reviews);
    }

    public function test_product_review_rating_cast(): void
    {
        $review = ProductReview::factory()->create(['rating' => 4]);

        $this->assertIsInt($review->rating);
        $this->assertEquals(4, $review->rating);
    }

    public function test_product_review_soft_deletes(): void
    {
        $review = ProductReview::factory()->create();
        $review->delete();

        $this->assertSoftDeleted($review);
        $this->assertNull(ProductReview::query()->find($review->id));
        $this->assertNotNull(ProductReview::withTrashed()->find($review->id));
    }
}
