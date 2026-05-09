<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\SeoMeta;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Автогенерація slug якщо порожній
        if (empty($data['slug']) && ! empty($data['title'])) {
            $urlService = new \App\Services\UrlRouterService;
            $title = is_array($data['title']) ? ($data['title']['uk'] ?? reset($data['title'])) : $data['title'];
            $data['slug'] = $urlService->generateSlug($title);
        }

        $this->seoData = [
            'title' => $data['seo_title'] ?? null,
            'description' => $data['seo_description'] ?? null,
            'keywords' => $data['seo_keywords'] ?? null,
        ];

        // Slug залишається в основних даних товару
        unset($data['seo_title'], $data['seo_description'], $data['seo_keywords']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Автоматично генеруємо SEO дані для нового товару
        $generator = new \App\Services\SeoMetaGenerator;
        $autoSeoData = $generator->generateForProduct($this->record, 'uk');

        $seoDataToSave = [];

        // Використовуємо вручну введені дані або автогенеровані
        if (! empty($this->seoData['title'])) {
            $seoDataToSave['meta_title'] = $this->seoData['title'];
            $isManual = true;
        } else {
            $seoDataToSave['meta_title'] = $autoSeoData['meta_title'];
            $isManual = false;
        }

        if (! empty($this->seoData['description'])) {
            $seoDataToSave['meta_description'] = $this->seoData['description'];
            $isManual = true;
        } else {
            $seoDataToSave['meta_description'] = $autoSeoData['meta_description'];
        }

        if (! empty($this->seoData['keywords']) && is_array($this->seoData['keywords'])) {
            $seoDataToSave['meta_keywords'] = implode(', ', $this->seoData['keywords']);
            $isManual = true;
        } else {
            $seoDataToSave['meta_keywords'] = $autoSeoData['meta_keywords'];
        }

        // Завжди створюємо SEO дані для нового товару
        SeoMeta::create(array_merge($seoDataToSave, [
            'seoable_type' => \App\Models\Product::class,
            'seoable_id' => $this->record->id,
            'page_type' => 'product',
            'url_slug' => $this->record->slug,
            'language' => 'uk',
            'robots_index' => true,
            'robots_follow' => true,
            'is_active' => true,
            'priority' => 0.8,
            'changefreq' => 'daily',
            'auto_generated' => ! ($isManual ?? false),
        ]));
    }

    private array $seoData = [];
}
