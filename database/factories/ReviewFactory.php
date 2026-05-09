<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'product_id' => \App\Models\Product::factory(),
            'author_name' => $this->faker->name(),
            'author_email' => $this->faker->email(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->paragraphs(rand(1, 3), true),
            'is_verified_purchase' => $this->faker->boolean(30),
            'status' => \App\Models\Review::STATUS_APPROVED,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Models\Review::STATUS_APPROVED,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Models\Review::STATUS_PENDING,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => \App\Models\Review::STATUS_REJECTED,
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => true,
        ]);
    }

    public function withAdminReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'admin_reply' => $this->faker->sentence(),
            'admin_replied_at' => now(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'author_name' => 'Анонімний покупець',
            'author_email' => null,
            'is_verified_purchase' => false,
        ]);
    }
}
