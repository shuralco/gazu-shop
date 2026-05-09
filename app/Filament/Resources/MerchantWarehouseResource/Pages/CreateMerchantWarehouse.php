<?php

namespace App\Filament\Resources\MerchantWarehouseResource\Pages;

use App\Filament\Resources\MerchantWarehouseResource;
use App\Models\MerchantWarehouse;
use Filament\Resources\Pages\CreateRecord;

class CreateMerchantWarehouse extends CreateRecord
{
    protected static string $resource = MerchantWarehouseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Only one default allowed.
        if (! empty($data['is_default'])) {
            MerchantWarehouse::query()->update(['is_default' => false]);
        }

        return $data;
    }
}
