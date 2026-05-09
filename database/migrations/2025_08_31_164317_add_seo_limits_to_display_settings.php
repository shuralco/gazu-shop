<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert SEO limits settings
        DB::table('display_settings')->insert([
            [
                'key' => 'seo_title_min_length',
                'value' => '10',
                'type' => 'number',
                'group' => 'seo_limits',
                'title' => 'Мінімальна довжина заголовка',
                'description' => 'Мінімальна кількість символів для SEO заголовка',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seo_title_max_length',
                'value' => '60',
                'type' => 'number',
                'group' => 'seo_limits',
                'title' => 'Максимальна довжина заголовка',
                'description' => 'Максимальна кількість символів для SEO заголовка',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seo_description_min_length',
                'value' => '50',
                'type' => 'number',
                'group' => 'seo_limits',
                'title' => 'Мінімальна довжина опису',
                'description' => 'Мінімальна кількість символів для SEO опису',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seo_description_max_length',
                'value' => '160',
                'type' => 'number',
                'group' => 'seo_limits',
                'title' => 'Максимальна довжина опису',
                'description' => 'Максимальна кількість символів для SEO опису',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'seo_keywords_max_count',
                'value' => '10',
                'type' => 'number',
                'group' => 'seo_limits',
                'title' => 'Максимальна кількість ключових слів',
                'description' => 'Максимальна кількість ключових слів для SEO',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove SEO limits settings
        DB::table('display_settings')
            ->where('group', 'seo_limits')
            ->delete();
    }
};
