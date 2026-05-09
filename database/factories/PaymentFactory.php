<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'gateway' => fake()->randomElement(['liqpay', 'wayforpay', 'fondy', 'stripe']),
            'external_id' => 'txn_'.fake()->uuid(),
            'status' => fake()->randomElement(['pending', 'processing', 'success', 'failed']),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'UAH',
            'fee_amount' => fake()->randomFloat(2, 5, 100),
            'metadata' => [
                'gateway_response' => fake()->word(),
                'user_ip' => fake()->ipv4(),
            ],
            'webhook_received_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'processed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
