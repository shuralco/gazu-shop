<?php

namespace App\Filament\Resources\BlogResource\Pages;

use App\Filament\Resources\BlogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\LocaleSwitcher::make()];
    }

    /** Завжди позначаємо як blog_post, щоб з'являлось на /blog. */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['template'] = 'blog_post';
        return $data;
    }
}
