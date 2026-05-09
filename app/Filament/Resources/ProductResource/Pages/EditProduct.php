<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\SeoMeta;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = ProductResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Товар';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-shopping-bag';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
        ];
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
            'seoable_type' => \App\Models\Product::class,
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
                    'seoable_type' => \App\Models\Product::class,
                    'seoable_id' => $this->record->id,
                    'language' => 'uk',
                ],
                array_merge($seoData, [
                    'page_type' => 'product',
                    'url_slug' => $this->record->slug,
                    'robots_index' => true,
                    'robots_follow' => true,
                    'is_active' => true,
                    'priority' => 0.8,
                    'changefreq' => 'daily',
                    'auto_generated' => ! $isManualEdit,
                ])
            );
        }
    }
}
