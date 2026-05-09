<?php

namespace Database\Factories;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $types = [Coupon::TYPE_PERCENTAGE, Coupon::TYPE_FIXED_AMOUNT, Coupon::TYPE_FREE_SHIPPING];
        $type = $this->faker->randomElement($types);

        $value = match ($type) {
            Coupon::TYPE_PERCENTAGE => $this->faker->numberBetween(5, 50),
            Coupon::TYPE_FIXED_AMOUNT => $this->faker->randomFloat(2, 10, 200),
            Coupon::TYPE_FREE_SHIPPING => 0,
        };

        return [
            'code' => strtoupper($this->faker->unique()->bothify('???###')),
            'type' => $type,
            'value' => $value,
            'minimum_amount' => $this->faker->optional(0.3)->randomFloat(2, 50, 500),
            'maximum_discount' => $this->faker->optional(0.2)->randomFloat(2, 20, 100),
            'usage_limit' => $this->faker->optional(0.4)->numberBetween(1, 100),
            'used_count' => 0,
            'usage_limit_per_user' => $this->faker->optional(0.3)->numberBetween(1, 5),
            'is_active' => true,
            'valid_from' => Carbon::yesterday(),
            'valid_until' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'valid_from' => Carbon::now()->subDays(10),
            'valid_until' => Carbon::yesterday(),
        ]);
    }

    public function percentage(float $value = 10.0): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Coupon::TYPE_PERCENTAGE,
            'value' => $value,
        ]);
    }

    public function fixedAmount(float $value = 50.0): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Coupon::TYPE_FIXED_AMOUNT,
            'value' => $value,
        ]);
    }

    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Coupon::TYPE_FREE_SHIPPING,
            'value' => 0,
        ]);
    }
}
