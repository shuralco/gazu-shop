<?php

namespace App\Filament\Resources\HelpArticleResource\Pages;

use App\Filament\Resources\HelpArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpArticle extends EditRecord
{
    protected static string $resource = HelpArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('open')
                ->label('Відкрити в довідці')
                ->icon('heroicon-m-eye')
                ->url(fn () => url('/admin/help?topic='.$this->record->slug))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
