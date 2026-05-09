<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DisplaySetting>
 */
class DisplaySettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'value' => $this->faker->randomElement([
                $this->faker->word(),
                $this->faker->numberBetween(1, 10),
                $this->faker->boolean(),
                [$this->faker->word(), $this->faker->word()],
            ]),
            'type' => $this->faker->randomElement(['string', 'number', 'boolean', 'array']),
            'group' => $this->faker->randomElement(['header_top_bar', 'mega_menu_content', 'seo', 'general']),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
