<?php

namespace App\Models;

/**
 * Backward-compat alias scoped to provider='novaposhta'.
 * New code should use App\Models\ShippingApiLog directly.
 */
class NpApiLog extends ShippingApiLog
{
    protected $attributes = [
        'provider' => 'novaposhta',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('only_novaposhta', function ($query) {
            $query->where('provider', 'novaposhta');
        });

        static::creating(function (NpApiLog $log) {
            if (empty($log->provider)) {
                $log->provider = 'novaposhta';
            }
        });
    }
}
