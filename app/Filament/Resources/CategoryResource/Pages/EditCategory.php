<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\SeoMeta;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Завантажити SEO дані з SeoMeta таблиці
        $seoMeta = SeoMeta::where([
            'seoable_type' => \App\Models\Category::class,
            'seoable_id' => $this->record->id,
            'language' => 'uk',
        ])->first();

        if ($seoMeta) {
            $data['seo_title'] = $seoMeta->meta_title;
            $data['seo_description'] = $seoMeta->meta_description;
            $data['seo_keywords'] = $seoMeta->meta_keywords ? explode(', ', $seoMeta->meta_keywords) : [];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['seo_title']) || isset($data['seo_description']) || isset($data['seo_keywords'])) {
            $this->saveSeoMeta($data);
        }

        unset($data['seo_title'], $data['seo_description'], $data['seo_keywords']);

        return $data;
    }

    private function saveSeoMeta(array $data): void
    {
        $seoData = [];
        $isManualEdit = false;

        $existingSeoMeta = SeoMeta::where([
            'seoable_type' => \App\Models\Category::class,
            'seoable_id' => $this->record->id,
            'language' => 'uk',
        ])->first();

        if (isset($data['seo_title'])) {
            $seoData['meta_title'] = $data['seo_title'];
            if ($existingSeoMeta && $existingSeoMeta->meta_title !== $data['seo_title']) {
                $isManualEdit = true;
            }
        }

        if (isset($data['seo_description'])) {
            $seoData['meta_description'] = $data['seo_description'];
            if ($existingSeoMeta && $existingSeoMeta->meta_description !== $data['seo_description']) {
                $isManualEdit = true;
            }
        }

        if (isset($data['seo_keywords']) && is_array($data['seo_keywords'])) {
            $seoData['meta_keywords'] = implode(', ', $data['seo_keywords']);
            $existingKeywords = $existingSeoMeta ? $existingSeoMeta->meta_keywords : '';
            if ($existingSeoMeta && $existingKeywords !== $seoData['meta_keywords']) {
                $isManualEdit = true;
            }
        }

        if (! empty($seoData)) {
            SeoMeta::updateOrCreate(
                [
                    'seoable_type' => \App\Models\Category::class,
                    'seoable_id' => $this->record->id,
                    'language' => 'uk',
                ],
                array_merge($seoData, [
                    'page_type' => 'category',
                    'url_slug' => $this->record->slug,
                    'robots_index' => true,
                    'robots_follow' => true,
                    'is_active' => true,
                    'priority' => 0.7,
                    'changefreq' => 'weekly',
                    'auto_generated' => ! $isManualEdit,
                ])
            );
        }
    }
}
