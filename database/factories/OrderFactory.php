<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'user_id' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $this->faker->optional(0.7)->firstName(),
            'email' => $this->faker->email(),
            'phone' => '+380'.$this->faker->numerify('#########'),
            'total' => $this->faker->randomFloat(2, 50, 1000),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'note' => $this->faker->optional()->sentence(),
            'shipping_data' => json_encode([
                'provider' => 'novaposhta',
                'method' => $this->faker->randomElement(['warehouse', 'courier', 'postomat']),
                'city_ref' => $this->faker->uuid(),
                'city_name' => $this->faker->city(),
            ]),
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }

    public function withUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user ? $user->id : User::factory()->create()->id,
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function withNovaPoshtaWarehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_data' => json_encode([
                'provider' => 'novaposhta',
                'method' => 'warehouse',
                'city_ref' => $this->faker->uuid(),
                'city_name' => 'Київ',
                'warehouse_ref' => $this->faker->uuid(),
                'warehouse_name' => 'Відділення №1: вул. Хрещатик, 1',
            ]),
        ]);
    }

    public function withNovaPoshtaCourier(): static
    {
        return $this->state(fn (array $attributes) => [
            'shipping_data' => json_encode([
                'provider' => 'novaposhta',
                'method' => 'courier',
                'city_ref' => $this->faker->uuid(),
                'city_name' => 'Харків',
                'address' => $this->faker->streetAddress(),
            ]),
        ]);
    }
}
