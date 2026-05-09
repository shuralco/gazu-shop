<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeoMeta>
 */
class SeoMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seoable_type' => $this->faker->randomElement(['App\\Models\\Product', 'App\\Models\\Category']),
            'seoable_id' => $this->faker->numberBetween(1, 100),
            'page_type' => $this->faker->randomElement(['product', 'category', 'home', 'search']),
            'url_slug' => $this->faker->slug(),
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->text(160),
            'meta_keywords' => $this->faker->words(5, true),
            'h1_title' => $this->faker->sentence(4),
            'canonical_url' => $this->faker->url(),
            'og_title' => $this->faker->sentence(4),
            'og_description' => $this->faker->text(120),
            'og_image' => '/assets/img/og-image.jpg',
            'og_type' => 'product',
            'twitter_title' => $this->faker->sentence(4),
            'twitter_description' => $this->faker->text(120),
            'twitter_image' => '/assets/img/twitter-image.jpg',
            'twitter_card' => 'summary_large_image',
            'robots_index' => $this->faker->boolean(90),
            'robots_follow' => $this->faker->boolean(95),
            'robots_custom' => null,
            'priority' => $this->faker->randomFloat(1, 0.1, 1.0),
            'changefreq' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'language' => 'uk',
            'auto_generated' => $this->faker->boolean(70),
            'is_active' => $this->faker->boolean(90),
            'structured_data' => [
                '@type' => 'Product',
                'name' => $this->faker->words(3, true),
                'description' => $this->faker->text(),
            ],
            'seo_text' => $this->faker->paragraph(),
        ];
    }
}
