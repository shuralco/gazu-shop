<?php

namespace App\Services;

use App\Models\BatchEditorLog;
use App\Models\Product;
use App\Models\ProductGroupPrice;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BatchEditorService
{
    public function updateProducts(array $changes): int
    {
        $count = 0;
        DB::transaction(function () use ($changes, &$count) {
            foreach ($changes as $id => $data) {
                $product = Product::find($id);
                if (!$product) continue;
                $product->fill($data);
                if ($product->isDirty()) {
                    $product->save();
                    $count++;
                }
            }
        });
        return $count;
    }

    public function batchUpdatePrice(array $ids, string $type, float $value): int
    {
        $oldValues = Product::whereIn('id', $ids)->pluck('price', 'id')->toArray();
        $query = Product::whereIn('id', $ids);

        $count = match ($type) {
            'set' => $query->update(['price' => $value]),
            'increase' => $query->increment('price', $value),
            'decrease' => $query->decrement('price', $value),
            'increase_percent' => DB::table('products')->whereIn('id', $ids)
                ->update(['price' => DB::raw("ROUND(price * (1 + {$value} / 100.0), 2)")]),
            'decrease_percent' => DB::table('products')->whereIn('id', $ids)
                ->update(['price' => DB::raw("ROUND(price * (1 - {$value} / 100.0), 2)")]),
            default => 0,
        };

        $this->log('price_change', "Ціна: {$type} {$value}", $ids, ['old_prices' => $oldValues]);

        return $count;
    }

    public function batchSetSale(array $ids, string $discountType, float $value): int
    {
        $oldValues = [];
        $count = 0;
        Product::whereIn('id', $ids)->chunk(100, function ($products) use ($discountType, $value, &$count, &$oldValues) {
            foreach ($products as $product) {
                $oldValues[$product->id] = ['price' => $product->price, 'old_price' => $product->old_price];
                $oldPrice = $product->price;
                $newPrice = $discountType === 'percent'
                    ? round($oldPrice * (1 - $value / 100), 2)
                    : max(0, $oldPrice - $value);

                $product->update([
                    'old_price' => $oldPrice,
                    'price' => $newPrice,
                ]);
                $count++;
            }
        });

        $this->log('sale', "Акція: {$discountType} {$value}", $ids, $oldValues);

        return $count;
    }

    public function batchRemoveSale(array $ids): int
    {
        $count = Product::whereIn('id', $ids)
            ->where('old_price', '>', 0)
            ->update(['price' => DB::raw('old_price'), 'old_price' => 0]);

        $this->log('sale_remove', "Акції знято", $ids);

        return $count;
    }

    public function batchSetGroupPrices(array $ids, int $groupId, string $type, float $value): int
    {
        $count = 0;
        Product::whereIn('id', $ids)->chunk(100, function ($products) use ($groupId, $type, $value, &$count) {
            foreach ($products as $product) {
                $price = $type === 'percent'
                    ? round($product->price * (1 - $value / 100), 2)
                    : $value;

                ProductGroupPrice::updateOrCreate(
                    ['product_id' => $product->id, 'customer_group_id' => $groupId],
                    ['price' => $price, 'min_quantity' => 1]
                );
                $count++;
            }
        });

        $this->log('group_price', "Гуртова ціна: група {$groupId}, {$type} {$value}", $ids);

        return $count;
    }

    public function batchUpdateStatus(array $ids, array $statuses): int
    {
        $fields = array_keys($statuses);
        $oldValues = Product::whereIn('id', $ids)->get()->mapWithKeys(function ($product) use ($fields) {
            return [$product->id => collect($fields)->mapWithKeys(fn ($f) => [$f => $product->{$f}])->toArray()];
        })->toArray();

        $count = Product::whereIn('id', $ids)->update($statuses);
        $desc = collect($statuses)->map(fn ($v, $k) => "{$k}=" . ($v ? 'true' : 'false'))->implode(', ');
        $this->log('status', "Статус: {$desc}", $ids, $oldValues);
        return $count;
    }

    public function batchUpdateCategory(array $ids, int $categoryId): int
    {
        $count = Product::whereIn('id', $ids)->update(['category_id' => $categoryId]);
        $this->log('category', "Категорія: {$categoryId}", $ids);
        return $count;
    }

    public function batchUpdateBrand(array $ids, ?int $brandId, ?string $manufacturer = null): int
    {
        $data = [];
        if ($brandId !== null) $data['brand_id'] = $brandId;
        if ($manufacturer !== null) $data['manufacturer'] = $manufacturer;
        $count = Product::whereIn('id', $ids)->update($data);

        $this->log('brand', "Бренд/виробник оновлено", $ids);

        return $count;
    }

    public function batchAttachFilters(array $productIds, array $filterIds): int
    {
        $count = 0;
        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if ($product) {
                $product->filters()->syncWithoutDetaching($filterIds);
                $count++;
            }
        }

        $this->log('filters_attach', "Фільтри додано", $productIds);

        return $count;
    }

    public function batchDetachFilters(array $productIds, array $filterIds): int
    {
        $count = 0;
        foreach ($productIds as $productId) {
            $product = Product::find($productId);
            if ($product) {
                $product->filters()->detach($filterIds);
                $count++;
            }
        }

        $this->log('filters_detach', "Фільтри видалено", $productIds);

        return $count;
    }

    public function batchGenerateVariants(array $productIds): int
    {
        $totalCreated = 0;

        $products = Product::whereIn('id', $productIds)
            ->with(['options' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                     'options.values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                     'variants'])
            ->get();

        foreach ($products as $product) {
            $options = $product->options;
            if ($options->isEmpty()) continue;

            $optionValueSets = $options->map(fn ($option) => $option->values->toArray())->toArray();
            $combinations = $this->cartesianProduct($optionValueSets);

            foreach ($combinations as $combination) {
                $optionValuesJson = [];
                $valueIds = [];
                $skuParts = [$product->sku ?? 'PRD-' . $product->id];

                foreach ($combination as $index => $valueData) {
                    $option = $options[$index];
                    $optionValuesJson[$option->name] = $valueData['value'];
                    $valueIds[] = $valueData['id'];
                    $skuParts[] = Str::upper(Str::limit(Str::slug($valueData['value'], ''), 6, ''));
                }

                $existingVariant = $product->variants()
                    ->whereJsonContains('option_values', $optionValuesJson)
                    ->first();

                if ($existingVariant) continue;

                $sku = implode('-', $skuParts);
                $counter = 0;
                $originalSku = $sku;
                while (ProductVariant::where('sku', $sku)->exists()) {
                    $counter++;
                    $sku = $originalSku . '-' . $counter;
                }

                $variant = $product->variants()->create([
                    'sku' => $sku,
                    'price' => null,
                    'quantity' => 0,
                    'stock_status' => 'in_stock',
                    'option_values' => $optionValuesJson,
                    'is_active' => true,
                ]);

                $variant->optionValues()->attach($valueIds);
                $totalCreated++;
            }
        }

        return $totalCreated;
    }

    private function cartesianProduct(array $sets): array
    {
        if (empty($sets)) return [];

        $result = [[]];
        foreach ($sets as $set) {
            $newResult = [];
            foreach ($result as $existing) {
                foreach ($set as $item) {
                    $newResult[] = array_merge($existing, [$item]);
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    public function searchReplace(array $ids, string $field, string $search, string $replace, bool $caseSensitive = false, bool $useRegex = false): array
    {
        $allowedFields = ['title', 'excerpt', 'content', 'meta_title', 'meta_description', 'name', 'manufacturer'];
        if (!in_array($field, $allowedFields)) return ['count' => 0, 'preview' => []];

        $products = Product::whereIn('id', $ids)->get();
        $preview = [];
        $count = 0;
        $oldValues = [];

        foreach ($products as $product) {
            $original = $product->{$field} ?? '';
            if ($useRegex) {
                $new = @preg_replace($search, $replace, $original);
                if ($new === null) continue;
            } elseif ($caseSensitive) {
                $new = str_replace($search, $replace, $original);
            } else {
                $new = str_ireplace($search, $replace, $original);
            }

            if ($original !== $new) {
                $oldValues[$product->id] = [$field => $original];
                $preview[] = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'original' => Str::limit($original, 100),
                    'new' => Str::limit($new, 100),
                ];
                $product->{$field} = $new;
                $product->save();
                $count++;
            }
        }

        if ($count > 0) {
            $affectedIds = collect($preview)->pluck('id')->toArray();
            $this->log('search_replace', "Пошук: {$search} → {$replace} (поле: {$field})", $affectedIds, $oldValues);
        }

        return ['count' => $count, 'preview' => $preview];
    }

    public function duplicateProducts(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $product = Product::find($id);
            if (!$product) continue;

            $new = $product->replicate();
            $new->title = '(Копія) ' . $product->title;
            $new->slug = null;
            $new->sku = null;
            $new->save();
            $count++;
        }

        $this->log('duplicate', "Товари дубльовано", $ids);

        return $count;
    }

    // ===== Preview Methods =====

    public function previewGroupPrice(array $ids, int $groupId, string $type, float $value): array
    {
        $group = \App\Models\CustomerGroup::find($groupId);
        $products = Product::whereIn('id', $ids)->select('id', 'title', 'price')->take(20)->get();
        $preview = [];
        foreach ($products as $product) {
            $newPrice = $type === 'percent'
                ? round($product->price * (1 - $value / 100), 2)
                : $value;
            $preview[] = [
                'id' => $product->id,
                'title' => Str::limit($product->title, 40),
                'old' => number_format($product->price, 2) . ' (роздрібна)',
                'new' => number_format($newPrice, 2) . ' (' . ($group->display_name ?? 'група') . ')',
            ];
        }
        return $preview;
    }

    public function previewPriceChange(array $ids, string $type, float $value): array
    {
        $products = Product::whereIn('id', $ids)->select('id', 'title', 'price')->get();
        $preview = [];
        foreach ($products->take(20) as $product) {
            $newPrice = match ($type) {
                'set' => $value,
                'increase' => $product->price + $value,
                'decrease' => max(0, $product->price - $value),
                'increase_percent' => round($product->price * (1 + $value / 100), 2),
                'decrease_percent' => round($product->price * (1 - $value / 100), 2),
                default => $product->price,
            };
            $preview[] = [
                'id' => $product->id,
                'title' => Str::limit($product->title, 40),
                'old' => number_format($product->price, 2),
                'new' => number_format($newPrice, 2),
            ];
        }
        return $preview;
    }

    public function previewSale(array $ids, string $discountType, float $value): array
    {
        $products = Product::whereIn('id', $ids)->select('id', 'title', 'price')->get();
        $preview = [];
        foreach ($products->take(20) as $product) {
            $newPrice = $discountType === 'percent'
                ? round($product->price * (1 - $value / 100), 2)
                : max(0, $product->price - $value);
            $preview[] = [
                'id' => $product->id,
                'title' => Str::limit($product->title, 40),
                'old' => number_format($product->price, 2),
                'new' => number_format($newPrice, 2),
                'discount' => number_format($product->price - $newPrice, 2),
            ];
        }
        return $preview;
    }

    public function previewSearchReplace(array $ids, string $field, string $search, string $replace, bool $caseSensitive = false, bool $useRegex = false): array
    {
        $allowedFields = ['title', 'excerpt', 'content', 'meta_title', 'meta_description', 'name', 'manufacturer'];
        if (!in_array($field, $allowedFields)) return [];

        $products = Product::whereIn('id', $ids)->get();
        $preview = [];
        foreach ($products as $product) {
            $original = $product->{$field} ?? '';
            if ($useRegex) {
                $new = @preg_replace($search, $replace, $original);
                if ($new === null) continue;
            } else {
                $new = $caseSensitive ? str_replace($search, $replace, $original) : str_ireplace($search, $replace, $original);
            }
            if ($original !== $new) {
                $preview[] = [
                    'id' => $product->id,
                    'title' => Str::limit($product->title, 40),
                    'original' => Str::limit($original, 60),
                    'new' => Str::limit($new, 60),
                ];
            }
        }
        return $preview;
    }

    // ===== Rollback =====

    public function rollback(int $logId): bool
    {
        $log = BatchEditorLog::find($logId);
        if (!$log || $log->rolled_back || empty($log->changes_data)) return false;

        $oldValues = $log->changes_data;

        // Handle price_change format: { old_prices: { id: price } }
        if ($log->action_type === 'price_change' && isset($oldValues['old_prices'])) {
            foreach ($oldValues['old_prices'] as $productId => $price) {
                Product::where('id', $productId)->update(['price' => $price]);
            }
            $log->update(['rolled_back' => true]);
            return true;
        }

        // Handle other formats: { id: { field: value, ... } }
        foreach ($oldValues as $productId => $fields) {
            $product = Product::find($productId);
            if (!$product) continue;

            if (is_array($fields)) {
                $product->update($fields);
            }
        }

        $log->update(['rolled_back' => true]);
        return true;
    }

    // ===== Logging =====

    private function log(string $actionType, string $description, array $ids, ?array $changes = null): void
    {
        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => $actionType,
            'description' => $description,
            'affected_ids' => $ids,
            'changes_data' => $changes,
            'affected_count' => count($ids),
            'created_at' => now(),
        ]);
    }

    public function exportCsv(array $ids, array $columns = []): StreamedResponse
    {
        $defaultColumns = ['id', 'title', 'sku', 'price', 'old_price', 'quantity', 'stock_status', 'is_active', 'is_hit', 'is_new', 'category_id', 'brand_id', 'manufacturer', 'weight'];
        $columns = !empty($columns) ? $columns : $defaultColumns;

        return response()->streamDownload(function () use ($ids, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            Product::whereIn('id', $ids)->chunk(100, function ($products) use ($handle, $columns) {
                foreach ($products as $product) {
                    $row = [];
                    foreach ($columns as $col) {
                        $row[] = $product->{$col} ?? '';
                    }
                    fputcsv($handle, $row);
                }
            });
            fclose($handle);
        }, 'batch-export-' . now()->format('Y-m-d-His') . '.csv');
    }
}
