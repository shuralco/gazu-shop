<?php

namespace App\Livewire\Product;

use App\Helpers\Category\Category;
use App\Helpers\Traits\CartTrait;
use App\Models\DisplaySetting;
use App\Models\FilterGroup;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Review;
use App\Traits\BrandDisplayTrait;
use Livewire\Component;

class ProductComponent extends Component
{
    use BrandDisplayTrait, CartTrait;

    protected $listeners = ['close-quick-order-modal' => 'closeQuickOrderModal'];

    public string $slug = '';

    public string $quickOrderName = '';

    public string $quickOrderPhone = '';

    public bool $quickOrderSuccess = false;

    public bool $showQuickOrderModal = false;

    public ?int $successOrderId = null;

    public string $reviewComment = '';

    public int $reviewRating = 5;

    public bool $showReviewForm = false;

    public string $activeTab = 'description';

    public ?int $selectedVariantId = null;

    public array $selectedOptions = [];

    public ?float $variantPrice = null;

    public ?string $variantSku = null;

    public bool $variantInStock = true;

    public function mount($product_slug = null, $product = null, $slug = null)
    {
        $rawSlug = $product_slug ?? $slug ?? ($product instanceof Product ? null : $product) ?? '';

        if ($product instanceof Product) {
            $locale = app()->getLocale();
            $this->slug = $product->getLocalizedSlug($locale) ?: $product->slug;
            return;
        }

        if (!$rawSlug) {
            abort(404);
        }

        // Resolve product by locale-specific slug
        $locale = app()->getLocale();
        $resolved = Product::findBySlug($rawSlug, $locale);

        if (!$resolved) {
            abort(404);
        }

        $this->slug = $resolved->getLocalizedSlug($locale) ?: $rawSlug;
    }

    public function getTotalPriceProperty()
    {
        $product = $this->getProduct();
        $unitPrice = $this->variantPrice ?? ($product ? (float) $product->price : 0);

        return $unitPrice * $this->quantity;
    }

    public function selectOption(int $optionId, int $valueId): void
    {
        $this->selectedOptions[$optionId] = $valueId;
        $this->findMatchingVariant();
    }

    private function findMatchingVariant(): void
    {
        $product = $this->getProduct();

        if (!$product) {
            return;
        }

        $selectedValueIds = collect($this->selectedOptions)->values()->sort()->values()->toArray();

        $activeVariants = $product->variants
            ->where('is_active', true);

        foreach ($activeVariants as $variant) {
            $variantValueIds = $variant->optionValues->pluck('id')->sort()->values()->toArray();

            if ($variantValueIds === $selectedValueIds) {
                $this->selectedVariantId = $variant->id;
                $this->variantPrice = $variant->getEffectivePrice();
                $this->variantSku = $variant->sku;
                $this->variantInStock = $variant->isInStock();
                return;
            }
        }

        $this->selectedVariantId = null;
        $this->variantPrice = null;
        $this->variantSku = null;
        $this->variantInStock = true;
    }

    public function increaseQuantity()
    {
        $this->quantity++;
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function openQuickOrderModal(): void
    {
        $this->showQuickOrderModal = true;
        $this->quickOrderSuccess = false;
        $this->successOrderId = null;
    }

    public function closeQuickOrderModal(): void
    {
        $this->showQuickOrderModal = false;
        $this->resetQuickOrder();
    }

    public function quickOrder()
    {
        $this->validate([
            'quickOrderName' => 'required|string|max:255',
            'quickOrderPhone' => 'required|string|max:20',
        ]);

        $product = $this->getProduct();

        $order = Order::create([
            'first_name' => $this->quickOrderName,
            'phone' => $this->quickOrderPhone,
            'email' => '',
            'total' => $this->totalPrice,
            'status' => 'pending',
            'note' => 'Швидке замовлення з товарної сторінки',
        ]);

        $variant = $this->selectedVariantId
            ? \App\Models\ProductVariant::find($this->selectedVariantId)
            : null;

        $orderTitle = $product->title;
        if ($variant) {
            $orderTitle .= ' (' . $variant->getDisplayName() . ')';
        }

        $orderPrice = $this->variantPrice ?? $product->price;

        OrderProduct::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'title' => $orderTitle,
            'image' => $variant?->image ?: ($product->image ?: 'default-product.jpg'),
            'slug' => $product->getLocalizedSlug(),
            'quantity' => $this->quantity,
            'price' => $orderPrice,
        ]);

        $this->quickOrderSuccess = true;
        $this->successOrderId = $order->id;
        // Modal stays open to show success state
    }

    public function openQuickOrder(): void
    {
        $this->showQuickOrderModal = true;
    }

    public function closeQuickOrder(): void
    {
        $this->showQuickOrderModal = false;
        $this->resetQuickOrder();
    }

    public function resetQuickOrder(): void
    {
        $this->quickOrderSuccess = false;
        $this->successOrderId = null;
        $this->quickOrderName = '';
        $this->quickOrderPhone = '';
        $this->quantity = 1;
        $this->reviewComment = '';
        $this->reviewRating = 5;
        $this->showReviewForm = false;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function toggleReviewForm(): void
    {
        $this->showReviewForm = ! $this->showReviewForm;
        if ($this->showReviewForm) {
            $this->activeTab = 'reviews';
        }
        if (! $this->showReviewForm) {
            $this->reviewComment = '';
            $this->reviewRating = 5;
        }
    }

    public function submitReview(): void
    {
        $minLength = (int) DisplaySetting::get('reviews_min_length', 10);

        $this->validate([
            'reviewComment' => "required|string|min:{$minLength}|max:1000",
            'reviewRating' => 'required|integer|min:1|max:5',
        ]);

        $product = $this->getProduct();

        $authorName = auth()->check() ? auth()->user()->name : 'Анонімний покупець';
        $authorEmail = auth()->check() ? auth()->user()->email : null;
        $userId = auth()->check() ? auth()->id() : null;

        $isVerifiedPurchase = $this->isVerifiedPurchase($product, $userId);
        $status = Review::determineInitialStatus($this->reviewComment, $isVerifiedPurchase);

        Review::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'author_name' => $authorName,
            'author_email' => $authorEmail,
            'rating' => $this->reviewRating,
            'comment' => $this->reviewComment,
            'is_verified_purchase' => $isVerifiedPurchase,
            'status' => $status,
        ]);

        // Update product rating (only approved reviews count)
        $product->updateRatingFromReviews();

        $message = $status === Review::STATUS_APPROVED
            ? 'Ваш відгук успішно додано'
            : 'Ваш відгук додано та буде відображено після модерації';

        $this->dispatch('show-notification',
            'success',
            'ВІДГУК ДОДАНО',
            $message
        );

        $this->toggleReviewForm();
    }

    private function isVerifiedPurchase(Product $product, ?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return OrderProduct::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('product_id', $product->id)->exists();
    }

    /**
     * Load product by locale-specific slug. NOT cached as a property to avoid
     * Livewire serialization issues with HasTranslations trait.
     */
    private function getProduct(): Product
    {
        return once(function () {
            $locale = app()->getLocale();

            // Try locale-specific JSON slug first
            $product = Product::query()
                ->where("slug->{$locale}", $this->slug)
                ->with(['brandModel', 'category', 'options.values', 'variants.optionValues'])
                ->first();

            if ($product) {
                return $product;
            }

            // Fallback: try any locale
            foreach (config('slugs.locales', ['uk', 'en']) as $loc) {
                if ($loc === $locale) {
                    continue;
                }
                $product = Product::query()
                    ->where("slug->{$loc}", $this->slug)
                    ->with(['brandModel', 'category', 'options.values', 'variants.optionValues'])
                    ->first();
                if ($product) {
                    return $product;
                }
            }

            // Legacy fallback: plain slug
            $product = Product::query()
                ->where('slug', $this->slug)
                ->with(['brandModel', 'category', 'options.values', 'variants.optionValues'])
                ->first();

            if ($product) {
                return $product;
            }

            abort(404);
        });
    }

    public function render()
    {
        $product = $this->getProduct();
        app(\App\Services\RecentlyViewedService::class)->add($product->id);
        $related_products = $product->relatedProducts()
            ->where('is_active', true)
            ->with(['brandModel', 'filters.filterGroup'])
            ->take(8)
            ->get();

        if ($related_products->isEmpty()) {
            $related_products = Product::query()
                ->where('category_id', '=', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->with(['brandModel', 'filters.filterGroup'])
                ->limit(8)
                ->get();
        }

        $breadcrumbs = Category::getBreadcrumbs($product->category_id);

        // Використовуємо різний синтаксис для MySQL і SQLite
        $dbDriver = config('database.default');
        if ($dbDriver === 'sqlite') {
            $attributes = FilterGroup::query()
                ->selectRaw('filter_groups.title as filter_groups_title, GROUP_CONCAT(filters.title, ", ") as filters_title')
                ->join('filters', 'filters.filter_group_id', '=', 'filter_groups.id')
                ->join('filter_products', 'filter_products.filter_id', '=', 'filters.id')
                ->where('filter_products.product_id', '=', $product->id)
                ->groupBy('filter_groups.title')
                ->get();
        } else {
            $attributes = FilterGroup::query()
                ->selectRaw('filter_groups.title as filter_groups_title, GROUP_CONCAT(filters.title SEPARATOR ", ") as filters_title')
                ->join('filters', 'filters.filter_group_id', '=', 'filter_groups.id')
                ->join('filter_products', 'filter_products.filter_id', '=', 'filters.id')
                ->where('filter_products.product_id', '=', $product->id)
                ->groupBy('filter_groups.title')
                ->get();
        }

        // Load reviews with pagination
        $reviews = $product->approvedReviews()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        // Calculate rating distribution (single grouped query instead of 5 separate COUNT queries)
        $counts = Review::selectRaw('rating, count(*) as cnt')
            ->where('product_id', $product->id)
            ->where('status', Review::STATUS_APPROVED)
            ->groupBy('rating')
            ->pluck('cnt', 'rating');
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = $counts[$i] ?? 0;
        }

        return view('livewire.product.product-component', [
            'product' => $product,
            'related_products' => $related_products,
            'breadcrumbs' => $breadcrumbs,
            'attributes' => $attributes,
            'reviews' => $reviews,
            'ratingDistribution' => $ratingDistribution,
            'title' => "Product {$product->title}",
        ])->extends('components.layouts.app', [
            'seoModel' => $product,
            'seoTitle' => $product->getSeoTitle(),
            'seoDescription' => $product->getSeoDescription(),
            'seoKeywords' => $product->getSeoKeywords(),
        ]);
    }
}
