<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Models\Review;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('На модерації')
                ->icon('heroicon-o-clock')
                ->badge(Review::where('status', Review::STATUS_PENDING)->count() ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Review::STATUS_PENDING)),

            'approved' => Tab::make('Схвалені')
                ->icon('heroicon-o-check-circle')
                ->badge(Review::where('status', Review::STATUS_APPROVED)->count() ?: null)
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Review::STATUS_APPROVED)),

            'rejected' => Tab::make('Відхилені')
                ->icon('heroicon-o-x-circle')
                ->badge(Review::where('status', Review::STATUS_REJECTED)->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Review::STATUS_REJECTED)),

            'all' => Tab::make('Всі')
                ->icon('heroicon-o-list-bullet')
                ->badge(Review::count() ?: null),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
