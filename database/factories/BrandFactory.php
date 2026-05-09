<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = [
            'Apple', 'Samsung', 'Nike', 'Adidas', 'Sony', 'LG', 'Xiaomi', 'Canon',
            'Nikon', 'Dell', 'HP', 'Asus', 'Lenovo', 'Philips', 'Bosch', 'Siemens',
            'Zara', 'H&M', 'Uniqlo', 'IKEA', 'Levis', 'Puma', 'Reebok', 'Converse',
            'Huawei', 'OnePlus', 'Google', 'Microsoft', 'Intel', 'AMD', 'Nvidia',
        ];

        $name = $this->faker->randomElement($brands);

        return [
            'name' => $name,
            'slug' => \Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'logo' => null,
            'description' => $this->faker->paragraph(),
            'meta_title' => $name.' - купити товари бренду онлайн',
            'meta_description' => 'Широкий асортимент товарів бренду '.$name.'. Оригінальна продукція, швидка доставка по Україні.',
            'meta_keywords' => \Str::lower($name).', товари '.\Str::lower($name).', купити '.\Str::lower($name),
            'is_active' => $this->faker->boolean(90),
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
