<?php

namespace Tests\Feature\Shop;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productId = \App\Models\Product::factory()->create()->id;
    }

    private function review(array $attrs = []): Review
    {
        return Review::create(array_merge([
            'product_id' => $this->productId,
            'author_name' => 'Тест',
            'author_email' => 't@example.com',
            'rating' => 5,
            'comment' => 'Чудова деталь',
            'status' => Review::STATUS_PENDING,
        ], $attrs));
    }

    public function test_new_review_is_pending(): void
    {
        $this->assertSame(Review::STATUS_PENDING, $this->review()->status);
    }

    public function test_approved_scope_filters(): void
    {
        $this->review(['status' => Review::STATUS_APPROVED]);
        $this->review(['status' => Review::STATUS_PENDING]);
        $this->review(['status' => Review::STATUS_REJECTED]);

        $this->assertSame(1, Review::approved()->count());
        $this->assertSame(1, Review::pending()->count());
        $this->assertSame(1, Review::rejected()->count());
    }

    public function test_approve_changes_status(): void
    {
        $r = $this->review();
        $r->approve();
        $this->assertSame(Review::STATUS_APPROVED, $r->fresh()->status);
    }

    public function test_stars_accessor(): void
    {
        $this->assertSame('★★★★☆', $this->review(['rating' => 4])->stars);
    }

    public function test_status_label_and_color(): void
    {
        $r = $this->review(['status' => Review::STATUS_APPROVED]);
        $this->assertSame('Схвалено', $r->status_label);
        $this->assertSame('success', $r->status_color);
    }
}
