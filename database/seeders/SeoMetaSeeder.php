<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SeoMetaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding SEO meta data...');

        $generator = new \App\Services\SeoMetaGenerator;

        $this->seedStaticPages($generator);
        $this->seedCategoriesAndProducts($generator);

        $this->command->info('SEO meta data seeded successfully!');
    }

    private function seedStaticPages(\App\Services\SeoMetaGenerator $generator): void
    {
        $staticPages = [
            'homepage' => [],
            'specials' => [],
            'hits' => [],
            'new' => [],
            'about' => [],
            'contacts' => [],
            'delivery' => [],
            'payment' => [],
            'privacy' => [],
            'terms' => [],
        ];

        foreach (['uk', 'en'] as $language) {
            foreach ($staticPages as $pageType => $data) {
                $seoData = $generator->generateForPage($pageType, $data, $language);

                \App\Models\SeoMeta::updateOrCreate([
                    'page_type' => $pageType,
                    'language' => $language,
                    'url_slug' => $pageType === 'homepage' ? '' : $pageType,
                ], array_merge($seoData, [
                    'is_active' => true,
                    'auto_generated' => true,
                    'robots_index' => true,
                    'robots_follow' => true,
                    'priority' => $this->getPagePriority($pageType),
                    'changefreq' => $this->getPageChangefreq($pageType),
                ]));
            }
        }
    }

    private function seedCategoriesAndProducts(\App\Services\SeoMetaGenerator $generator): void
    {
        foreach (['uk', 'en'] as $language) {
            $generator->generateBulkForCategories($language);
            $generator->generateBulkForProducts($language);
        }
    }

    private function getPagePriority(string $pageType): float
    {
        return match ($pageType) {
            'homepage' => 1.0,
            'specials', 'hits', 'new' => 0.9,
            'about', 'contacts' => 0.6,
            'delivery', 'payment' => 0.5,
            default => 0.4,
        };
    }

    private function getPageChangefreq(string $pageType): string
    {
        return match ($pageType) {
            'homepage' => 'daily',
            'specials', 'hits', 'new' => 'daily',
            'about', 'contacts', 'delivery', 'payment' => 'monthly',
            default => 'weekly',
        };
    }
}
