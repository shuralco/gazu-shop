<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Convert existing string values to JSON format {"uk": "value"}
        // Step 2: Change column type to JSON

        $this->convertToTranslatable('products', ['title', 'excerpt', 'meta_title', 'meta_description']);
        $this->convertToTranslatable('categories', ['title', 'meta_title', 'meta_description']);
        $this->convertToTranslatable('brands', ['name']);
        $this->convertToTranslatable('faq_pages', ['title', 'description']);
    }

    public function down(): void
    {
        // Extract uk value back to plain string
        $this->revertFromTranslatable('products', ['title', 'excerpt', 'meta_title', 'meta_description']);
        $this->revertFromTranslatable('categories', ['title', 'meta_title', 'meta_description']);
        $this->revertFromTranslatable('brands', ['name']);
        $this->revertFromTranslatable('faq_pages', ['title', 'description']);
    }

    private function convertToTranslatable(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            // Drop indexes on this column
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
            foreach ($indexes as $index) {
                if ($index->Key_name !== 'PRIMARY') {
                    try {
                        DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index->Key_name}`");
                    } catch (\Throwable $e) {
                    }
                }
            }

            // Wrap existing values in JSON: "value" → '{"uk":"value"}'
            // Skip values that are already JSON (start with {)
            DB::statement("
                UPDATE `{$table}`
                SET `{$column}` = CONCAT('{\"uk\":', JSON_QUOTE(`{$column}`), '}')
                WHERE `{$column}` IS NOT NULL
                AND `{$column}` != ''
                AND `{$column}` NOT LIKE '{%'
            ");

            // Change column type to LONGTEXT (safe for JSON, no index issues)
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` LONGTEXT NULL");
        }
    }

    private function revertFromTranslatable(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            // Extract uk value from JSON
            DB::statement("
                UPDATE `{$table}`
                SET `{$column}` = JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, '$.uk'))
                WHERE `{$column}` IS NOT NULL
                AND `{$column}` LIKE '{%'
                AND JSON_VALID(`{$column}`)
            ");

            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(255) NULL");
        }
    }
};
