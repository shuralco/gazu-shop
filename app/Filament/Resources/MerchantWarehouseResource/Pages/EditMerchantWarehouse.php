<?php

namespace App\Filament\Resources\MerchantWarehouseResource\Pages;

use App\Filament\Resources\MerchantWarehouseResource;
use App\Models\MerchantWarehouse;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMerchantWarehouse extends EditRecord
{
    protected static string $resource = MerchantWarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->is_default),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['is_default'])) {
            MerchantWarehouse::query()
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        return $data;
    }
}
