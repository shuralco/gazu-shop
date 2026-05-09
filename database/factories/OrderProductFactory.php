<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderProductFactory extends Factory
{
    protected $model = OrderProduct::class;

    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'title' => $product->title,
            'slug' => $product->getLocalizedSlug('uk'),
            'price' => $product->price,
            'quantity' => $this->faker->numberBetween(1, 5),
            'image' => $product->image ?? 'assets/img/no-image.png',
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'title' => $product->title,
            'slug' => $product->getLocalizedSlug('uk'),
            'price' => $product->price,
            'image' => $product->image ?? 'assets/img/no-image.png',
        ]);
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }
}
