<?php

namespace Database\Factories;

use App\Models\FilterGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilterGroupFactory extends Factory
{
    protected $model = FilterGroup::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement(['Color', 'Size', 'Brand', 'Material']),
            'is_active' => true, // Завжди активні в тестах для безпеки
        ];
    }
}
