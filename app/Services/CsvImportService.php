<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CsvImportService
{
    private const FIELD_MAP = [
        'title' => 'Назва',
        'sku' => 'SKU (артикул)',
        'price' => 'Ціна',
        'old_price' => 'Стара ціна',
        'quantity' => 'Кількість',
        'stock_status' => 'Наявність',
        'is_active' => 'Активний',
        'is_hit' => 'Хіт',
        'is_new' => 'Новинка',
        'excerpt' => 'Короткий опис',
        'content' => 'Повний опис',
        'manufacturer' => 'Виробник',
        'weight' => 'Вага (кг)',
        'dimensions' => 'Розміри',
        'barcode' => 'Штрихкод',
        'meta_title' => 'SEO Title',
        'meta_description' => 'SEO Description',
        'category_id' => 'Категорія (ID)',
        'brand_id' => 'Бренд (ID)',
    ];

    public function parsePreview(string $path, int $rows = 5): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return ['headers' => [], 'rows' => [], 'total_rows' => 0];

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return ['headers' => [], 'rows' => [], 'total_rows' => 0];
        }

        $data = [];
        $count = 0;
        $totalRows = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $totalRows++;
            if ($count < $rows) {
                $data[] = $row;
                $count++;
            }
        }
        fclose($handle);

        // Auto-detect mapping based on header names
        $autoMapping = $this->autoDetectMapping($headers);

        return [
            'headers' => $headers,
            'rows' => $data,
            'total_rows' => $totalRows,
            'auto_mapping' => $autoMapping,
        ];
    }

    public function getAvailableFields(): array
    {
        return array_keys(self::FIELD_MAP);
    }

    public function getFieldLabels(): array
    {
        return self::FIELD_MAP;
    }

    public function getAvailableColumns(): array
    {
        return self::FIELD_MAP;
    }

    public function import(string $path, array $mapping, bool $updateExisting = true): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => 0, 'error_messages' => []];

        $headers = fgetcsv($handle);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $errorMessages = [];
        $lineNumber = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                try {
                    $data = [];
                    foreach ($mapping as $csvIndex => $fieldName) {
                        if ($fieldName && $fieldName !== 'skip' && isset($row[$csvIndex])) {
                            $data[$fieldName] = trim($row[$csvIndex]);
                        }
                    }

                    if (empty($data['title']) && empty($data['sku'])) {
                        continue;
                    }

                    // Convert types
                    if (isset($data['price'])) $data['price'] = (float) str_replace([',', ' '], ['.', ''], $data['price']);
                    if (isset($data['old_price'])) $data['old_price'] = (float) str_replace([',', ' '], ['.', ''], $data['old_price']);
                    if (isset($data['quantity'])) $data['quantity'] = (int) $data['quantity'];
                    if (isset($data['weight'])) $data['weight'] = (float) str_replace(',', '.', $data['weight']);
                    if (isset($data['category_id'])) $data['category_id'] = (int) $data['category_id'];
                    if (isset($data['brand_id'])) $data['brand_id'] = (int) $data['brand_id'];
                    if (isset($data['is_active'])) $data['is_active'] = in_array(strtolower($data['is_active']), ['1', 'true', 'yes', 'так']);
                    if (isset($data['is_hit'])) $data['is_hit'] = in_array(strtolower($data['is_hit']), ['1', 'true', 'yes', 'так']);
                    if (isset($data['is_new'])) $data['is_new'] = in_array(strtolower($data['is_new']), ['1', 'true', 'yes', 'так']);

                    // Validate category_id if present
                    if (!empty($data['category_id']) && !Category::where('id', $data['category_id'])->exists()) {
                        $errorMessages[] = "Рядок {$lineNumber}: категорія ID={$data['category_id']} не існує";
                        $errors++;
                        continue;
                    }

                    // Validate brand_id if present
                    if (!empty($data['brand_id']) && !Brand::where('id', $data['brand_id'])->exists()) {
                        $errorMessages[] = "Рядок {$lineNumber}: бренд ID={$data['brand_id']} не існує";
                        $errors++;
                        continue;
                    }

                    // Find existing by SKU
                    $product = null;
                    if (!empty($data['sku'])) {
                        $product = Product::where('sku', $data['sku'])->first();
                    }

                    if ($product) {
                        if ($updateExisting) {
                            $product->update($data);
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        if (empty($data['title'])) {
                            $errorMessages[] = "Рядок {$lineNumber}: відсутня назва товару";
                            $errors++;
                            continue;
                        }
                        if (!isset($data['price'])) {
                            $data['price'] = 0;
                        }
                        if (!isset($data['category_id'])) {
                            $data['category_id'] = Category::first()?->id ?? 1;
                        }
                        Product::create($data);
                        $created++;
                    }
                } catch (\Throwable $e) {
                    $errorMessages[] = "Рядок {$lineNumber}: " . Str::limit($e->getMessage(), 100);
                    $errors++;
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $errorMessages[] = "Критична помилка: " . Str::limit($e->getMessage(), 200);
            $errors++;
        }

        fclose($handle);

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'error_messages' => array_slice($errorMessages, 0, 20),
        ];
    }

    private function autoDetectMapping(array $headers): array
    {
        $aliases = [
            'title' => ['title', 'name', 'назва', 'найменування', 'товар', 'product', 'наименование'],
            'sku' => ['sku', 'артикул', 'article', 'code', 'код'],
            'price' => ['price', 'ціна', 'цена', 'cost', 'вартість'],
            'old_price' => ['old_price', 'стара ціна', 'старая цена', 'sale_price', 'compare_price'],
            'quantity' => ['quantity', 'qty', 'кількість', 'количество', 'stock', 'залишок'],
            'stock_status' => ['stock_status', 'наявність', 'наличие', 'availability'],
            'is_active' => ['is_active', 'active', 'активний', 'активный', 'статус'],
            'is_hit' => ['is_hit', 'hit', 'хіт', 'хит', 'bestseller'],
            'is_new' => ['is_new', 'new', 'новинка', 'нова'],
            'excerpt' => ['excerpt', 'short_description', 'короткий опис', 'краткое описание'],
            'content' => ['content', 'description', 'опис', 'описание', 'full_description'],
            'manufacturer' => ['manufacturer', 'виробник', 'производитель'],
            'weight' => ['weight', 'вага', 'вес'],
            'dimensions' => ['dimensions', 'розміри', 'размеры'],
            'barcode' => ['barcode', 'штрихкод', 'ean', 'upc'],
            'meta_title' => ['meta_title', 'seo_title'],
            'meta_description' => ['meta_description', 'seo_description'],
            'category_id' => ['category_id', 'категорія', 'категория', 'category'],
            'brand_id' => ['brand_id', 'бренд', 'brand'],
        ];

        $mapping = [];
        foreach ($headers as $index => $header) {
            $normalized = mb_strtolower(trim($header));
            $matched = 'skip';
            foreach ($aliases as $field => $aliasList) {
                if (in_array($normalized, $aliasList, true)) {
                    $matched = $field;
                    break;
                }
            }
            $mapping[$index] = $matched;
        }

        return $mapping;
    }
}
