<?php

namespace Database\Factories;

use App\Models\Filter;
use App\Models\FilterGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class FilterFactory extends Factory
{
    protected $model = Filter::class;

    public function definition(): array
    {
        return [
            'filter_group_id' => FilterGroup::factory(),
            'title' => $this->faker->word(),
            'value' => $this->faker->word(),
            'is_active' => true, // Завжди активні в тестах для безпеки
        ];
    }

    public function forGroup(FilterGroup $group): static
    {
        return $this->state(fn (array $attributes) => [
            'filter_group_id' => $group->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement(['Red', 'Blue', 'Green', 'Black', 'White']),
            'value' => $this->faker->randomElement(['red', 'blue', 'green', 'black', 'white']),
        ]);
    }

    public function size(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'value' => $this->faker->randomElement(['xs', 's', 'm', 'l', 'xl', 'xxl']),
        ]);
    }
}
