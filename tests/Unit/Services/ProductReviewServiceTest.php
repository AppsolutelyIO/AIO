<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use App\Models\User;
use Appsolutely\AIO\Enums\ReviewStatus;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductReview;
use Appsolutely\AIO\Services\ProductReviewService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductReviewService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductReviewService::class);
    }

    public function test_create_review(): void
    {
        $product = Product::factory()->create();
        $user    = User::factory()->create();

        $review = $this->service->createReview([
            'product_id' => $product->id,
            'user_id'    => $user->id,
            'rating'     => 5,
            'title'      => 'Great product',
            'body'       => 'Really loved it',
        ]);

        $this->assertInstanceOf(ProductReview::class, $review);
        $this->assertEquals(5, $review->rating);
        $this->assertEquals(ReviewStatus::Pending, $review->status);
    }

    public function test_get_average_rating_for_approved_reviews(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 5]);
        ProductReview::factory()->approved()->create(['product_id' => $product->id, 'rating' => 3]);
        ProductReview::factory()->create(['product_id' => $product->id, 'rating' => 1]); // pending, excluded

        $average = $this->service->getAverageRating($product->id);

        $this->assertEquals(4.0, $average);
    }

    public function test_get_average_rating_with_no_reviews(): void
    {
        $product = Product::factory()->create();

        $average = $this->service->getAverageRating($product->id);

        $this->assertEquals(0.0, $average);
    }
}
