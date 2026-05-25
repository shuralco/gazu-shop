<?php

namespace App\Events;

use App\Models\NpShipment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NpShipmentStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public NpShipment $shipment,
        public string $oldStatus,
        public string $newStatus,
    ) {
    }
}
