<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpScanSheet extends Model
{
    protected $table = 'up_scan_sheets';

    protected $fillable = [
        'uuid', 'name', 'shipments_count', 'shipment_uuids', 'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'shipment_uuids' => 'array',
            'printed_at' => 'datetime',
        ];
    }
}
