<?php

namespace App\Filament\Resources\NpScanSheetResource\Pages;

use App\Filament\Resources\NpScanSheetResource;
use App\Models\NpShipment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateNpScanSheet extends CreateRecord
{
    protected static string $resource = NpScanSheetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate temp ref for local linking until NP-create
        if (empty($data['ref'])) {
            $data['ref'] = 'local_' . Str::uuid()->toString();
        }
        return $data;
    }

    protected function afterCreate(): void
    {
        // Attach selected shipments to the sheet
        $shipmentRefs = $this->data['shipment_refs'] ?? [];
        if (! empty($shipmentRefs)) {
            NpShipment::whereIn('ref', $shipmentRefs)->update([
                'registry_ref' => $this->record->ref,
            ]);
            $this->record->recalculateTotals();
        }
    }
}
