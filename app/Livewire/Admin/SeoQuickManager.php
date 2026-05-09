<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Services\SeoMetaGenerator;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SeoQuickManager extends Component
{
    public $model;

    public $modelType;

    public $modelId;

    public $language = 'uk';

    public $seoTitle;

    public $seoDescription;

    public $canonicalUrl;

    public $robotsDirective;

    public $sitemapInclude = true;

    public $sitemapPriority = 0.7;

    public $editMode = false;

    /**
     * Fix for Livewire 3 toJSON error.
     * This method is called by JavaScript when trying to serialize the component.
     */
    public function toJSON(): string
    {
        return json_encode([
            'modelType' => $this->modelType,
            'modelId' => $this->modelId,
            'editMode' => $this->editMode,
            'componentName' => 'seo-quick-manager',
        ]);
    }

    public function mount($model = null)
    {
        if ($model) {
            $this->model = $model;
            $this->modelType = get_class($model);
            $this->modelId = $model->id;
            $this->loadSeoData();
        }
    }

    public function loadSeoData(): void
    {
        if (! $this->model) {
            return;
        }

        // Check if model is SeoMeta itself or has seoMeta relationship
        if ($this->model instanceof SeoMeta) {
            $seoMeta = $this->model;
        } else {
            $seoMeta = $this->model->seoMeta()->where('language', $this->language)->first();
        }

        if ($seoMeta) {
            $this->seoTitle = $seoMeta->meta_title;
            $this->seoDescription = $seoMeta->meta_description;
            $this->canonicalUrl = $seoMeta->canonical_url;
            $this->robotsDirective = $seoMeta->getRobotsDirective();
            $this->sitemapInclude = $seoMeta->sitemap_include ?? true;
            $this->sitemapPriority = $seoMeta->sitemap_priority ?? 0.7;
        } else {
            // Load from trait methods (only if model has HasSeoMeta trait)
            if (method_exists($this->model, 'getSeoTitle')) {
                $this->seoTitle = $this->model->getSeoTitle($this->language);
                $this->seoDescription = $this->model->getSeoDescription($this->language);
                $this->canonicalUrl = $this->model->getCanonicalUrl();
                $this->robotsDirective = $this->model->getRobotsDirective();
            }
        }
    }

    public function updatedLanguage(): void
    {
        $this->loadSeoData();
    }

    public function toggleEditMode(): void
    {
        $this->editMode = ! $this->editMode;

        if (! $this->editMode) {
            $this->loadSeoData(); // Reset if cancelled
        }
    }

    public function generateSeo(): void
    {
        if (! $this->model) {
            return;
        }

        $generator = new SeoMetaGenerator;

        if ($this->model instanceof Category) {
            $seoData = $generator->generateForCategory($this->model, $this->language);
        } elseif ($this->model instanceof Product) {
            $seoData = $generator->generateForProduct($this->model, $this->language);
        } else {
            return;
        }

        SeoMeta::updateOrCreate(
            [
                'seoable_type' => $this->modelType,
                'seoable_id' => $this->modelId,
                'language' => $this->language,
            ],
            array_merge($seoData, [
                'robots_index' => true,
                'robots_follow' => true,
                'is_active' => true,
                'sitemap_include' => true,
                'sitemap_priority' => $this->getSitemapPriority(),
                'sitemap_changefreq' => $this->getSitemapChangefreq(),
                'auto_generated' => true,
            ])
        );

        $this->loadSeoData();

        session()->flash('seo-generated', 'SEO дані згенеровано для '.$this->language);
    }

    public function saveSeo(): void
    {
        $this->validate([
            'seoTitle' => 'required|max:60',
            'seoDescription' => 'required|max:155',
        ]);

        SeoMeta::updateOrCreate(
            [
                'seoable_type' => $this->modelType,
                'seoable_id' => $this->modelId,
                'language' => $this->language,
            ],
            [
                'meta_title' => $this->seoTitle,
                'meta_description' => $this->seoDescription,
                'canonical_url' => $this->canonicalUrl,
                'robots_index' => str_contains($this->robotsDirective, 'noindex') ? false : true,
                'robots_follow' => str_contains($this->robotsDirective, 'nofollow') ? false : true,
                'sitemap_include' => $this->sitemapInclude,
                'sitemap_priority' => $this->sitemapPriority,
                'is_active' => true,
                'auto_generated' => false,
            ]
        );

        $this->editMode = false;
        Cache::flush(); // Clear SEO cache

        session()->flash('seo-saved', 'SEO дані збережено');
    }

    private function getSitemapPriority(): float
    {
        return match ($this->modelType) {
            Product::class => 0.8,
            Category::class => 0.7,
            default => 0.5,
        };
    }

    private function getSitemapChangefreq(): string
    {
        return match ($this->modelType) {
            Product::class => 'daily',
            Category::class => 'weekly',
            default => 'monthly',
        };
    }

    public function render()
    {
        return view('livewire.admin.seo-quick-manager');
    }
}
