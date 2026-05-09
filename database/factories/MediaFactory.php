<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'path' => 'assets/img/products/'.$this->faker->numberBetween(1, 20).'.jpg',
            'model_type' => 'App\\Models\\Product',
            'model_id' => \App\Models\Product::factory(),
        ];
    }
}
