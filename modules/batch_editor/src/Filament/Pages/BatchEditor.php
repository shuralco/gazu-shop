<?php

namespace App\Filament\Pages;

use App\Models\BatchEditorLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Filter;
use App\Models\FilterGroup;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\BatchEditorService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class BatchEditor extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Пакетний редактор';
    protected static ?string $navigationGroup = 'Обслуговування';
    protected static ?string $title = 'Пакетний редактор';
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.batch-editor';

    // Spreadsheet-стиль редактор — повна ширина контенту (без дефолтного
    // 7xl/screen-2xl обмеження, що лишало порожнечу по боках).
    protected ?string $maxContentWidth = 'full';

    // Filters
    public ?int $filterCategory = null;
    public ?int $filterBrand = null;
    public ?string $filterStatus = null;
    public ?string $filterSearch = '';
    public ?float $filterPriceFrom = null;
    public ?float $filterPriceTo = null;
    public string $activeTab = 'products';

    // Selection
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Edited data tracking
    public array $editedData = [];
    public array $editedCategoryData = [];

    // Tab-specific filters
    public string $orderStatusFilter = '';

    // Tab-specific modals
    public bool $showParentCategoryModal = false;
    public ?int $newParentCategoryId = null;
    public bool $showOrderStatusModal = false;
    public string $orderBatchStatus = '';

    // Modal states
    public bool $showPriceModal = false;
    public bool $showSaleModal = false;
    public bool $showGroupPriceModal = false;
    public bool $showStatusModal = false;
    public bool $showCategoryModal = false;
    public bool $showBrandModal = false;
    public bool $showFilterModal = false;
    public bool $showSearchReplaceModal = false;
    public bool $showWeightModal = false;

    // Modal form data
    public string $priceType = 'increase_percent';
    public float $priceValue = 0;
    public string $saleType = 'percent';
    public float $saleValue = 0;
    public ?int $groupPriceGroupId = null;
    public string $groupPriceType = 'percent';
    public float $groupPriceValue = 0;
    public ?string $statusField = 'is_active';
    public $statusValue = true;
    public ?int $newCategoryId = null;
    public ?int $newBrandId = null;
    public ?string $newManufacturer = null;
    public array $selectedFilterIds = [];
    public string $filterAction = 'attach';
    public ?int $filterGroupId = null;
    public string $srField = 'title';
    public string $srSearch = '';
    public string $srReplace = '';
    public bool $srCaseSensitive = false;
    public bool $srUseRegex = false;
    public array $srPreview = [];
    public ?float $newWeight = null;
    public ?string $newDimensions = null;

    // Import
    public bool $showImportModal = false;
    public $importFile = null;
    public array $importPreview = [];
    public array $importMapping = [];
    public array $importHeaders = [];
    public ?string $importResult = null;
    public int $importStep = 1;
    public int $importTotalRows = 0;
    public bool $importUpdateExisting = true;
    public array $importStats = [];

    // SEO
    public bool $showSeoModal = false;
    public string $seoField = 'meta_title';
    public string $seoTemplate = '';
    public string $seoAction = 'template';

    // Column visibility
    public array $visibleColumns = ['id', 'title', 'price', 'old_price', 'quantity', 'stock_status', 'is_active', 'category', 'brand'];

    // Additional filters
    public ?string $filterStockStatus = null;
    public ?string $filterManufacturer = null;

    // Extended filters (Sprint 1)
    public bool $filterNoImage = false;
    public bool $filterNoDescription = false;
    public bool $filterNoSeo = false;
    public bool $filterHasVariants = false;
    public bool $filterHasGroupPrice = false;
    public ?float $filterRatingFrom = null;
    public ?float $filterRatingTo = null;
    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;
    public ?int $filterQtyFrom = null;
    public ?int $filterQtyTo = null;
    public bool $showAdvancedFilters = false;

    // Variant generation
    public bool $showVariantModal = false;
    public array $variantPreview = [];

    // Preview (Sprint 2)
    public array $previewData = [];
    public bool $showPreview = false;
    public string $previewAction = '';

    public function getAvailableColumns(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Назва',
            'sku' => 'SKU',
            'price' => 'Ціна',
            'old_price' => 'Стара ціна',
            'quantity' => 'К-сть',
            'stock_status' => 'Наявність',
            'is_active' => 'Акт.',
            'is_hit' => 'Хіт',
            'is_new' => 'Нов.',
            'category' => 'Категорія',
            'brand' => 'Бренд',
            'manufacturer' => 'Виробник',
            'weight' => 'Вага',
            'rating' => 'Рейтинг',
            'reviews_count' => 'Відгуки',
            'created_at' => 'Створено',
        ];
    }

    public function getProducts()
    {
        $query = Product::query()->with(['category:id,title', 'brandModel:id,name']);

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }
        if ($this->filterBrand) {
            $query->where('brand_id', $this->filterBrand);
        }
        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->filterStatus === 'hit') {
            $query->where('is_hit', true);
        } elseif ($this->filterStatus === 'new') {
            $query->where('is_new', true);
        } elseif ($this->filterStatus === 'sale') {
            $query->where('old_price', '>', 0);
        }
        if ($this->filterPriceFrom) {
            $query->where('price', '>=', $this->filterPriceFrom);
        }
        if ($this->filterPriceTo) {
            $query->where('price', '<=', $this->filterPriceTo);
        }
        if ($this->filterSearch) {
            $search = $this->filterSearch;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }
        if ($this->filterStockStatus) {
            $query->where('stock_status', $this->filterStockStatus);
        }
        if ($this->filterManufacturer) {
            $query->where('manufacturer', 'LIKE', "%{$this->filterManufacturer}%");
        }

        // Extended filters
        if ($this->filterNoImage) {
            $query->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            });
        }
        if ($this->filterNoDescription) {
            $query->where(function ($q) {
                $q->whereNull('content')->orWhere('content', '');
            });
        }
        if ($this->filterNoSeo) {
            $query->where(function ($q) {
                $q->whereNull('meta_title')->orWhere('meta_title', '');
            });
        }
        if ($this->filterHasVariants) {
            $query->whereHas('variants');
        }
        if ($this->filterHasGroupPrice) {
            $query->whereHas('groupPrices');
        }
        if ($this->filterRatingFrom) {
            $query->where('rating', '>=', $this->filterRatingFrom);
        }
        if ($this->filterRatingTo) {
            $query->where('rating', '<=', $this->filterRatingTo);
        }
        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }
        if ($this->filterQtyFrom !== null) {
            $query->where('quantity', '>=', $this->filterQtyFrom);
        }
        if ($this->filterQtyTo !== null) {
            $query->where('quantity', '<=', $this->filterQtyTo);
        }

        return $query->orderBy('id', 'desc')->paginate(50);
    }

    public function getCategories()
    {
        return Category::orderBy('title')->pluck('title', 'id')->toArray();
    }

    public function getBrands()
    {
        return Brand::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getCustomerGroups()
    {
        return CustomerGroup::active()->pluck('display_name', 'id')->toArray();
    }

    public function getFilterGroups()
    {
        return FilterGroup::with('filters')->where('is_active', true)->get();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedIds = match ($this->activeTab) {
                'categories' => $this->getCategoryItems()->pluck('id')->toArray(),
                'orders' => $this->getOrderItems()->pluck('id')->toArray(),
                'reviews' => $this->getReviewItems()->pluck('id')->toArray(),
                default => $this->getProducts()->pluck('id')->toArray(),
            };
        } else {
            $this->selectedIds = [];
        }
    }

    public function updateField(int $productId, string $field, $value): void
    {
        $this->editedData[$productId][$field] = $value;
    }

    public function saveChanges(): void
    {
        if (empty($this->editedData)) {
            Notification::make()->warning()->title('Немає змін для збереження')->send();
            return;
        }

        $service = app(BatchEditorService::class);
        $count = $service->updateProducts($this->editedData);
        $this->editedData = [];

        Notification::make()->success()->title("Оновлено {$count} товарів")->send();
    }

    // Batch actions
    public function applyBatchPrice(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $service = app(BatchEditorService::class);
        $count = $service->batchUpdatePrice($ids, $this->priceType, $this->priceValue);
        $this->showPriceModal = false;
        $this->priceValue = 0;

        Notification::make()->success()->title("Ціни оновлено для {$count} товарів")->send();
    }

    public function applyBatchSale(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $service = app(BatchEditorService::class);
        $count = $service->batchSetSale($ids, $this->saleType, $this->saleValue);
        $this->showSaleModal = false;
        $this->saleValue = 0;

        Notification::make()->success()->title("Акція встановлена для {$count} товарів")->send();
    }

    public function removeSale(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $count = app(BatchEditorService::class)->batchRemoveSale($ids);
        Notification::make()->success()->title("Акція знята з {$count} товарів")->send();
    }

    public function applyGroupPrice(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || !$this->groupPriceGroupId) return;

        $service = app(BatchEditorService::class);
        $count = $service->batchSetGroupPrices($ids, $this->groupPriceGroupId, $this->groupPriceType, $this->groupPriceValue);
        $this->showGroupPriceModal = false;
        $this->groupPriceValue = 0;
        $this->groupPriceGroupId = null;

        Notification::make()->success()->title("Гуртові ціни встановлено для {$count} товарів")->send();
    }

    public function applyStatus(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $service = app(BatchEditorService::class);
        $value = in_array($this->statusValue, ['1', 'true', true, 1], true) ? true : false;
        $count = $service->batchUpdateStatus($ids, [$this->statusField => $value]);
        $this->showStatusModal = false;
        $this->statusField = 'is_active';
        $this->statusValue = true;

        Notification::make()->success()->title("Статус оновлено для {$count} товарів")->send();
    }

    public function applyCategory(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || !$this->newCategoryId) return;

        $count = app(BatchEditorService::class)->batchUpdateCategory($ids, $this->newCategoryId);
        $this->showCategoryModal = false;
        $this->newCategoryId = null;

        Notification::make()->success()->title("Категорія змінена для {$count} товарів")->send();
    }

    public function applyBrand(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $count = app(BatchEditorService::class)->batchUpdateBrand($ids, $this->newBrandId, $this->newManufacturer);
        $this->showBrandModal = false;
        $this->newBrandId = null;
        $this->newManufacturer = null;

        Notification::make()->success()->title("Бренд/виробник оновлено для {$count} товарів")->send();
    }

    public function applyFilters(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || empty($this->selectedFilterIds)) return;

        $service = app(BatchEditorService::class);
        $count = $this->filterAction === 'attach'
            ? $service->batchAttachFilters($ids, $this->selectedFilterIds)
            : $service->batchDetachFilters($ids, $this->selectedFilterIds);
        $this->showFilterModal = false;
        $this->selectedFilterIds = [];

        $action = $this->filterAction === 'attach' ? 'додано' : 'видалено';
        Notification::make()->success()->title("Фільтри {$action} для {$count} товарів")->send();
    }

    public function applySearchReplace(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || !$this->srSearch) return;

        $result = app(BatchEditorService::class)->searchReplace($ids, $this->srField, $this->srSearch, $this->srReplace, $this->srCaseSensitive, $this->srUseRegex);
        $this->srPreview = $result['preview'];
        $this->showSearchReplaceModal = false;
        $this->srSearch = '';
        $this->srReplace = '';

        Notification::make()->success()->title("Замінено в {$result['count']} товарах")->send();
    }

    public function applyWeight(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $data = [];
        if ($this->newWeight !== null) $data['weight'] = $this->newWeight;
        if ($this->newDimensions !== null) $data['dimensions'] = $this->newDimensions;

        $count = Product::whereIn('id', $ids)->update($data);

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'weight',
            'description' => 'Вага/розміри оновлено',
            'affected_ids' => $ids,
            'affected_count' => $count,
            'created_at' => now(),
        ]);

        $this->showWeightModal = false;
        $this->newWeight = null;
        $this->newDimensions = null;

        Notification::make()->success()->title("Вага/розміри оновлено для {$count} товарів")->send();
    }

    public function duplicateSelected(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $count = app(BatchEditorService::class)->duplicateProducts($ids);
        Notification::make()->success()->title("Дубльовано {$count} товарів")->send();
    }

    public function exportSelected()
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) {
            Notification::make()->warning()->title('Виберіть товари для експорту')->send();
            return;
        }

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'export',
            'description' => 'Експорт товарів',
            'affected_ids' => $ids,
            'affected_count' => count($ids),
            'created_at' => now(),
        ]);

        return app(BatchEditorService::class)->exportCsv($ids);
    }

    private function getSelectedIds(): array
    {
        if (empty($this->selectedIds)) {
            Notification::make()->warning()->title('Виберіть товари')->send();
            return [];
        }
        return $this->selectedIds;
    }

    // ===== Categories Tab =====
    public function getCategoryItems()
    {
        return Category::with('parent:id,title')
            ->orderBy('sort_order')
            ->paginate(50);
    }

    public function updateCategoryField(int $categoryId, string $field, $value): void
    {
        $this->editedCategoryData[$categoryId][$field] = $value;
    }

    public function saveCategoryChanges(): void
    {
        if (empty($this->editedCategoryData)) {
            Notification::make()->warning()->title('Немає змін для збереження')->send();
            return;
        }

        $count = 0;
        foreach ($this->editedCategoryData as $id => $data) {
            $category = Category::find($id);
            if (!$category) continue;
            $category->fill($data);
            if ($category->isDirty()) {
                $category->save();
                $count++;
            }
        }
        $this->editedCategoryData = [];

        Notification::make()->success()->title("Оновлено {$count} категорій")->send();
    }

    public function batchActivateCategories(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        Category::whereIn('id', $ids)->update(['is_active' => true]);
        Notification::make()->success()->title('Категорії активовано')->send();
        $this->selectedIds = [];
    }

    public function batchDeactivateCategories(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        Category::whereIn('id', $ids)->update(['is_active' => false]);
        Notification::make()->success()->title('Категорії деактивовано')->send();
        $this->selectedIds = [];
    }

    public function applyParentCategory(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        Category::whereIn('id', $ids)->update(['parent_id' => $this->newParentCategoryId ?: null]);
        $this->showParentCategoryModal = false;
        $this->newParentCategoryId = null;
        Notification::make()->success()->title('Батьківську категорію змінено')->send();
        $this->selectedIds = [];
    }

    // ===== Orders Tab =====
    public function getOrderItems()
    {
        $query = Order::with('user:id,name');
        if ($this->orderStatusFilter) {
            $query->where('status', $this->orderStatusFilter);
        }
        return $query->orderByDesc('id')->paginate(50);
    }

    public function batchChangeOrderStatus(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || !$this->orderBatchStatus) return;

        Order::whereIn('id', $ids)->update(['status' => $this->orderBatchStatus]);
        Notification::make()->success()->title('Статуси замовлень оновлено')->send();
        $this->selectedIds = [];
        $this->showOrderStatusModal = false;
        $this->orderBatchStatus = '';
    }

    public function exportOrders()
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) {
            Notification::make()->warning()->title('Виберіть замовлення для експорту')->send();
            return;
        }

        $columns = ['id', 'name', 'email', 'phone', 'total', 'status', 'payment_status', 'created_at'];

        return response()->streamDownload(function () use ($ids, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            Order::whereIn('id', $ids)->chunk(100, function ($orders) use ($handle, $columns) {
                foreach ($orders as $order) {
                    $row = [];
                    foreach ($columns as $col) {
                        $row[] = $order->{$col} ?? '';
                    }
                    fputcsv($handle, $row);
                }
            });
            fclose($handle);
        }, 'orders-export-' . now()->format('Y-m-d-His') . '.csv');
    }

    // ===== Reviews Tab =====
    public function getReviewItems()
    {
        return Review::with(['product:id,title'])
            ->orderByDesc('id')
            ->paginate(50);
    }

    public function batchApproveReviews(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        Review::whereIn('id', $ids)->update(['status' => Review::STATUS_APPROVED]);
        Notification::make()->success()->title('Відгуки схвалено')->send();
        $this->selectedIds = [];
    }

    public function batchRejectReviews(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        Review::whereIn('id', $ids)->delete();
        Notification::make()->success()->title('Відгуки видалено')->send();
        $this->selectedIds = [];
    }

    // ===== Import =====
    public function openImportModal(): void
    {
        $this->showImportModal = true;
        $this->importStep = 1;
        $this->importFile = null;
        $this->importPreview = [];
        $this->importMapping = [];
        $this->importHeaders = [];
        $this->importResult = null;
        $this->importTotalRows = 0;
        $this->importUpdateExisting = true;
        $this->importStats = [];
    }

    public function updatedImportFile(): void
    {
        if (!$this->importFile) return;

        $path = $this->importFile->getRealPath();
        $service = app(\App\Services\CsvImportService::class);
        $preview = $service->parsePreview($path);

        if (empty($preview['headers'])) {
            Notification::make()->danger()->title('Помилка читання CSV')->body('Файл порожній або пошкоджений')->send();
            return;
        }

        $this->importHeaders = $preview['headers'];
        $this->importPreview = $preview['rows'];
        $this->importTotalRows = $preview['total_rows'];
        $this->importMapping = $preview['auto_mapping'] ?? array_fill(0, count($this->importHeaders), 'skip');
        $this->importStep = 2;
    }

    public function executeImport(): void
    {
        if (!$this->importFile) return;

        $service = app(\App\Services\CsvImportService::class);
        $result = $service->import(
            $this->importFile->getRealPath(),
            $this->importMapping,
            $this->importUpdateExisting
        );

        $this->importStats = $result;
        $this->importStep = 3;

        $msg = "Створено: {$result['created']}, Оновлено: {$result['updated']}";
        if ($result['skipped'] > 0) {
            $msg .= ", Пропущено: {$result['skipped']}";
        }
        $msg .= ", Помилок: {$result['errors']}";

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'import',
            'description' => $msg,
            'affected_ids' => [],
            'affected_count' => $result['created'] + $result['updated'],
            'created_at' => now(),
        ]);

        $this->importResult = $msg;

        Notification::make()->success()->title('Імпорт завершено')->body($msg)->send();
    }

    public function resetImport(): void
    {
        $this->importStep = 1;
        $this->importFile = null;
        $this->importPreview = [];
        $this->importMapping = [];
        $this->importHeaders = [];
        $this->importResult = null;
        $this->importTotalRows = 0;
        $this->importStats = [];
    }

    public function getImportableFields(): array
    {
        return app(\App\Services\CsvImportService::class)->getAvailableFields();
    }

    public function getImportFieldLabels(): array
    {
        return app(\App\Services\CsvImportService::class)->getFieldLabels();
    }

    // ===== Delete =====
    public function deleteSelected(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'delete',
            'description' => 'Видалення товарів',
            'affected_ids' => $ids,
            'affected_count' => count($ids),
            'created_at' => now(),
        ]);

        Product::whereIn('id', $ids)->delete();
        $this->selectedIds = [];
        Notification::make()->success()->title(count($ids) . ' товарів видалено')->send();
    }

    // ===== SEO =====
    public function applySeoMeta(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        if ($this->seoAction === 'auto_generate') {
            $this->autoGenerateSeo($ids);
            return;
        }

        if (!$this->seoTemplate) {
            Notification::make()->warning()->title('Введіть шаблон')->send();
            return;
        }

        $count = 0;
        Product::whereIn('id', $ids)->with(['category:id,title', 'brandModel:id,name'])->chunk(50, function ($products) use (&$count) {
            foreach ($products as $product) {
                $value = str_replace(
                    ['{title}', '{brand}', '{category}', '{price}', '{sku}'],
                    [$product->title, $product->brandModel?->name ?? '', $product->category?->title ?? '', number_format($product->price, 0), $product->sku ?? ''],
                    $this->seoTemplate
                );

                if ($this->seoField === 'meta_keywords') {
                    $product->update(['meta_keywords' => array_map('trim', explode(',', $value))]);
                } else {
                    $product->update([$this->seoField => $value]);
                }
                $count++;
            }
        });

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'seo',
            'description' => "SEO шаблон ({$this->seoField}): {$this->seoTemplate}",
            'affected_ids' => $ids,
            'affected_count' => $count,
            'created_at' => now(),
        ]);

        $this->showSeoModal = false;
        $this->seoTemplate = '';
        $this->seoAction = 'template';
        Notification::make()->success()->title("SEO оновлено для {$count} товарів")->send();
    }

    private function autoGenerateSeo(array $ids): void
    {
        $generator = app(\App\Services\SeoMetaGenerator::class);
        $count = 0;

        Product::whereIn('id', $ids)->with('category')->chunk(50, function ($products) use ($generator, &$count) {
            foreach ($products as $product) {
                $seoData = $generator->generateForProduct($product);
                $updateData = [];

                if ($this->seoField === 'meta_title') {
                    $updateData['meta_title'] = $seoData['meta_title'];
                } elseif ($this->seoField === 'meta_description') {
                    $updateData['meta_description'] = $seoData['meta_description'];
                } elseif ($this->seoField === 'meta_keywords') {
                    $updateData['meta_keywords'] = array_map('trim', explode(',', $seoData['meta_keywords'] ?? ''));
                } else {
                    $updateData = [
                        'meta_title' => $seoData['meta_title'],
                        'meta_description' => $seoData['meta_description'],
                        'meta_keywords' => array_map('trim', explode(',', $seoData['meta_keywords'] ?? '')),
                    ];
                }

                $product->update($updateData);
                $count++;
            }
        });

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'seo_auto',
            'description' => "Авто-генерація SEO ({$this->seoField})",
            'affected_ids' => $ids,
            'affected_count' => $count,
            'created_at' => now(),
        ]);

        $this->showSeoModal = false;
        $this->seoTemplate = '';
        $this->seoAction = 'template';
        Notification::make()->success()->title("SEO авто-генерацію виконано для {$count} товарів")->send();
    }

    // ===== Variant Generation =====
    public function openVariantModal(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $products = Product::whereIn('id', $ids)
            ->with(['options' => fn ($q) => $q->where('is_active', true),
                     'options.values' => fn ($q) => $q->where('is_active', true),
                     'variants'])
            ->get();

        $this->variantPreview = [];
        foreach ($products as $product) {
            $options = $product->options;
            if ($options->isEmpty()) continue;

            $optionNames = $options->pluck('name')->toArray();
            $valueCounts = $options->map(fn ($o) => $o->values->count())->toArray();
            $totalCombinations = array_product($valueCounts) ?: 0;
            $existingVariants = $product->variants->count();

            $this->variantPreview[] = [
                'id' => $product->id,
                'title' => $product->title,
                'options' => implode(', ', $optionNames),
                'option_count' => count($optionNames),
                'combinations' => $totalCombinations,
                'existing' => $existingVariants,
                'new' => max(0, $totalCombinations - $existingVariants),
            ];
        }

        $this->showVariantModal = true;
    }

    public function generateVariants(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;

        $count = app(BatchEditorService::class)->batchGenerateVariants($ids);

        BatchEditorLog::create([
            'user_id' => auth()->id(),
            'action_type' => 'variant_generate',
            'description' => "Генерація варіантів: створено {$count}",
            'affected_ids' => $ids,
            'affected_count' => $count,
            'created_at' => now(),
        ]);

        $this->showVariantModal = false;
        $this->variantPreview = [];

        Notification::make()->success()->title("Згенеровано {$count} варіантів")->send();
    }

    public function updatedActiveTab(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterBrand(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedFilterSearch(): void { $this->resetPage(); }
    public function updatedOrderStatusFilter(): void { $this->resetPage(); }
    public function updatedFilterStockStatus(): void { $this->resetPage(); }
    public function updatedFilterManufacturer(): void { $this->resetPage(); }
    public function updatedFilterNoImage(): void { $this->resetPage(); }
    public function updatedFilterNoDescription(): void { $this->resetPage(); }
    public function updatedFilterNoSeo(): void { $this->resetPage(); }
    public function updatedFilterHasVariants(): void { $this->resetPage(); }
    public function updatedFilterHasGroupPrice(): void { $this->resetPage(); }
    public function updatedFilterRatingFrom(): void { $this->resetPage(); }
    public function updatedFilterRatingTo(): void { $this->resetPage(); }
    public function updatedFilterDateFrom(): void { $this->resetPage(); }
    public function updatedFilterDateTo(): void { $this->resetPage(); }
    public function updatedFilterQtyFrom(): void { $this->resetPage(); }
    public function updatedFilterQtyTo(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->filterCategory = null;
        $this->filterBrand = null;
        $this->filterStatus = null;
        $this->filterSearch = '';
        $this->filterPriceFrom = null;
        $this->filterPriceTo = null;
        $this->filterStockStatus = null;
        $this->filterManufacturer = null;
        $this->filterNoImage = false;
        $this->filterNoDescription = false;
        $this->filterNoSeo = false;
        $this->filterHasVariants = false;
        $this->filterHasGroupPrice = false;
        $this->filterRatingFrom = null;
        $this->filterRatingTo = null;
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->filterQtyFrom = null;
        $this->filterQtyTo = null;
        $this->orderStatusFilter = '';
        $this->resetPage();
    }

    private function resetPage(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    // ===== Preview (Sprint 2) =====
    public function previewGroupPrice(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || !$this->groupPriceGroupId) return;
        $this->previewData = app(BatchEditorService::class)->previewGroupPrice($ids, $this->groupPriceGroupId, $this->groupPriceType, $this->groupPriceValue);
        $this->previewAction = 'group_price';
        $this->showPreview = true;
    }

    public function previewPrice(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;
        $this->previewData = app(BatchEditorService::class)->previewPriceChange($ids, $this->priceType, $this->priceValue);
        $this->previewAction = 'price';
        $this->showPreview = true;
    }

    public function previewSale(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) return;
        $this->previewData = app(BatchEditorService::class)->previewSale($ids, $this->saleType, $this->saleValue);
        $this->previewAction = 'sale';
        $this->showPreview = true;
    }

    public function previewSR(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids) || !$this->srSearch) return;
        $this->previewData = app(BatchEditorService::class)->previewSearchReplace($ids, $this->srField, $this->srSearch, $this->srReplace, $this->srCaseSensitive, $this->srUseRegex);
        $this->previewAction = 'searchreplace';
        $this->showPreview = true;
    }

    public function confirmAndApply(): void
    {
        match ($this->previewAction) {
            'price' => $this->applyBatchPrice(),
            'sale' => $this->applyBatchSale(),
            'searchreplace' => $this->applySearchReplace(),
            'group_price' => $this->applyGroupPrice(),
            default => null,
        };
        $this->showPreview = false;
        $this->previewData = [];
        $this->previewAction = '';
    }

    public function cancelPreview(): void
    {
        $this->showPreview = false;
        $this->previewData = [];
        $this->previewAction = '';
    }

    // ===== Rollback =====
    public function rollbackAction(int $logId): void
    {
        $result = app(BatchEditorService::class)->rollback($logId);
        if ($result) {
            Notification::make()->success()->title('Операцію скасовано')->send();
        } else {
            Notification::make()->danger()->title('Неможливо скасувати')->body('Немає даних для відкату')->send();
        }
    }

    // ===== Journal (Sprint 2) =====
    public function getJournalItems()
    {
        return BatchEditorLog::with('user:id,name')
            ->orderByDesc('id')
            ->paginate(20);
    }
}
