<?php

namespace App\Filament\Resources\ReceivingOrderResource\Pages;

use App\Filament\Resources\ReceivingOrderResource;
use App\Models\ReceivingOrder;
use Filament\Resources\Pages\CreateRecord;

class CreateReceivingOrder extends CreateRecord
{
    protected static string $resource = ReceivingOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = ReceivingOrder::nextCode();
        $data['status'] = ReceivingOrder::STATUS_DRAFT;
        $data['created_by_user_id'] = auth()->id();

        return $data;
    }
}
