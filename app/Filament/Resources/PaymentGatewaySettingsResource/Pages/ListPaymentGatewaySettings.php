<?php

namespace App\Filament\Resources\PaymentGatewaySettingsResource\Pages;

use App\Filament\Resources\PaymentGatewaySettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentGatewaySettings extends ListRecords
{
    protected static string $resource = PaymentGatewaySettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
