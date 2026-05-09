<?php

namespace App\Filament\Resources\InventoryTransferResource\Pages;

use App\Filament\Resources\InventoryTransferResource;
use App\Models\InventoryTransfer;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryTransfer extends CreateRecord
{
    protected static string $resource = InventoryTransferResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = InventoryTransfer::nextCode();
        $data['status'] = InventoryTransfer::STATUS_DRAFT;
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
