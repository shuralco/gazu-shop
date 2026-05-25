<?php

namespace App\Filament\Resources\NpShipmentResource\Pages;

use App\Filament\Resources\NpShipmentResource;
use App\Models\NpShipment;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNpShipments extends ListRecords
{
    protected static string $resource = NpShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Створити ТТН'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Всі')
                ->badge(NpShipment::count())
                ->badgeColor('gray'),

            'new' => Tab::make('Нові')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', NpShipment::STATUS_NEW))
                ->badge(NpShipment::where('status', NpShipment::STATUS_NEW)->count())
                ->badgeColor('gray'),

            'created' => Tab::make('Створені')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', NpShipment::STATUS_CREATED))
                ->badge(NpShipment::where('status', NpShipment::STATUS_CREATED)->count())
                ->badgeColor('info'),

            'sent' => Tab::make('В дорозі')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', NpShipment::STATUS_SENT))
                ->badge(NpShipment::where('status', NpShipment::STATUS_SENT)->count())
                ->badgeColor('warning'),

            'delivered' => Tab::make('Доставлені')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', NpShipment::STATUS_DELIVERED))
                ->badge(NpShipment::where('status', NpShipment::STATUS_DELIVERED)->count())
                ->badgeColor('success'),

            'returned' => Tab::make('Повернені')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', NpShipment::STATUS_RETURNED))
                ->badge(NpShipment::where('status', NpShipment::STATUS_RETURNED)->count())
                ->badgeColor('danger'),
        ];
    }
}
