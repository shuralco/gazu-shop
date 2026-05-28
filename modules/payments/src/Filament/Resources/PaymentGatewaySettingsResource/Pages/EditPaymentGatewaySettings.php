<?php

namespace App\Filament\Resources\PaymentGatewaySettingsResource\Pages;

use App\Filament\Resources\PaymentGatewaySettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentGatewaySettings extends EditRecord
{
    protected static string $resource = PaymentGatewaySettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
