<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Unified API call log for all shipping providers (novaposhta, ukrposhta, ...).
 * Stored in `shipping_api_logs` table; rows are tagged via the `provider` column.
 */
class ShippingApiLog extends Model
{
    protected $table = 'shipping_api_logs';

    protected $fillable = [
        'provider',
        'endpoint_model',
        'endpoint_method',
        'success',
        'http_status',
        'duration_ms',
        'request_payload',
        'response_payload',
        'errors',
        'warnings',
        'caller',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'http_status' => 'integer',
            'duration_ms' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'errors' => 'array',
            'warnings' => 'array',
        ];
    }

    public function getEndpointAttribute(): string
    {
        return "{$this->endpoint_model}.{$this->endpoint_method}";
    }

    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_ms === null) {
            return '—';
        }
        if ($this->duration_ms < 1000) {
            return "{$this->duration_ms}ms";
        }

        return number_format($this->duration_ms / 1000, 2).'s';
    }

    public function getProviderLabelAttribute(): string
    {
        return match ($this->provider) {
            'novaposhta' => 'Нова Пошта',
            'ukrposhta' => 'УкрПошта',
            default => ucfirst((string) $this->provider),
        };
    }

    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Best-effort lookup of the NpShipment that triggered this API call.
     * Only meaningful for novaposhta provider entries.
     */
    public function getRelatedShipment(): ?NpShipment
    {
        if ($this->provider !== 'novaposhta') {
            return null;
        }

        $req = is_array($this->request_payload) ? $this->request_payload : [];
        $resp = is_array($this->response_payload) ? ($this->response_payload['data'][0] ?? []) : [];

        foreach ([$resp['Ref'] ?? null, $req['Ref'] ?? null, $req['DocumentRef'] ?? null] as $ref) {
            if ($ref) {
                $sh = NpShipment::where('ref', $ref)->first();
                if ($sh) {
                    return $sh;
                }
            }
        }

        foreach ([$resp['IntDocNumber'] ?? null, $req['IntDocNumber'] ?? null] as $ttn) {
            if ($ttn) {
                $sh = NpShipment::where('ttn', $ttn)->first();
                if ($sh) {
                    return $sh;
                }
            }
        }

        if (! empty($req['Documents']) && is_array($req['Documents'])) {
            foreach ($req['Documents'] as $d) {
                $ttn = is_array($d) ? ($d['DocumentNumber'] ?? null) : $d;
                if ($ttn) {
                    $sh = NpShipment::where('ttn', $ttn)->first();
                    if ($sh) {
                        return $sh;
                    }
                }
            }
        }

        if (! empty($req['Description']) && preg_match('/#(\d+)/', $req['Description'], $m)) {
            return NpShipment::where('order_id', (int) $m[1])->latest('id')->first();
        }

        return null;
    }
}
