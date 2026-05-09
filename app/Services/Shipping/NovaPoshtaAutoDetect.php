<?php

namespace App\Services\Shipping;

use App\Models\DisplaySetting;
use App\Models\NpCity;
use App\Models\ShippingProvider;
use App\Services\NovaPoshtaApiService;

/**
 * Auto-discover sender refs for the API-key owner via Counterparty endpoints.
 * Persists to BOTH shipping_providers.configuration (runtime) and
 * display_settings group=nova_poshta (so admin form can render readable values).
 */
class NovaPoshtaAutoDetect
{
    public function __construct(private NovaPoshtaApiService $api)
    {
    }

    /**
     * @return array{success:bool,errors:array,detected:array}
     */
    public function detectAndSave(): array
    {
        $detected = [];
        $errors = [];

        // 1. Sender Counterparty (own organization)
        $cp = $this->api->getCounterparties('Sender');
        if (empty($cp['success']) || empty($cp['data'])) {
            return [
                'success' => false,
                'errors' => $cp['errors'] ?? ['Counterparty not found — API key may have no sender registered'],
                'detected' => [],
            ];
        }
        $sender = $cp['data'][0];
        $detected['sender_ref'] = $sender['Ref'];
        $detected['sender_name'] = $sender['Description'] ?? '';
        $detected['edrpou'] = $sender['EDRPOU'] ?? '';

        // 2. Contact person (first one)
        $contacts = $this->api->getCounterpartyContactPersons($sender['Ref']);
        if (! empty($contacts['data'])) {
            $contact = $contacts['data'][0];
            $detected['sender_contact_ref'] = $contact['Ref'] ?? '';
            $detected['sender_contact_name'] = trim(($contact['LastName'] ?? '').' '.($contact['FirstName'] ?? '').' '.($contact['MiddleName'] ?? ''));
            $detected['sender_phone'] = $contact['Phones'] ?? '';
        } else {
            $errors[] = 'No contact persons found';
        }

        // 3. Sender address — Counterparty addresses are linked to a counterparty.
        // For NP API, this Ref is used as `SenderAddress` when creating a TTN.
        // It is NOT the same entity as a public Warehouse/Branch.
        $addresses = $this->api->getCounterpartyAddresses($sender['Ref']);
        if (! empty($addresses['data'])) {
            $addr = $addresses['data'][0];
            $detected['sender_address_ref'] = $addr['Ref'] ?? '';
            $detected['sender_warehouse_ref'] = $addr['Ref'] ?? '';
            $detected['sender_city_ref'] = $addr['CityRef'] ?? '';
            $detected['sender_address'] = $addr['Description'] ?? '';

            if (! empty($detected['sender_city_ref'])) {
                $cityName = NpCity::where('ref', $detected['sender_city_ref'])->value('description');
                if ($cityName) {
                    $detected['sender_city_name'] = $cityName;
                }
            }
        } else {
            $errors[] = 'No sender addresses found';
        }

        // Persist to ShippingProvider runtime config
        $provider = ShippingProvider::where('code', 'novaposhta')->first();
        if ($provider) {
            $cfg = $provider->configuration ?? [];
            foreach ($detected as $k => $v) {
                if ($v !== null && $v !== '') {
                    $cfg[$k] = $v;
                }
            }
            $provider->update(['configuration' => $cfg]);
        }

        // Mirror to DisplaySetting so the admin form shows readable names
        $mapping = [
            'sender_ref' => 'np_sender_ref',
            'sender_name' => 'np_sender_name',
            'sender_contact_ref' => 'np_contact_person_ref',
            'sender_contact_name' => 'np_contact_person',
            'sender_phone' => 'np_sender_phone',
            'sender_city_ref' => 'np_sender_city_ref',
            'sender_city_name' => 'np_sender_city_name',
            'sender_warehouse_ref' => 'np_sender_warehouse_ref',
            'sender_address' => 'np_sender_address',
            'edrpou' => 'np_sender_edrpou',
        ];

        // The `value` column has a JSON type; raw insert needs json_encode'd values.
        // Set type='string' explicitly so DisplaySetting::get() returns the raw decoded string.
        foreach ($mapping as $detectedKey => $settingKey) {
            if (! empty($detected[$detectedKey])) {
                \Illuminate\Support\Facades\DB::table('display_settings')
                    ->updateOrInsert(
                        ['key' => $settingKey],
                        [
                            'value' => json_encode((string) $detected[$detectedKey]),
                            'type' => 'string',
                            'group' => 'nova_poshta',
                            'title' => ucfirst(str_replace(['np_', '_'], ['', ' '], $settingKey)),
                            'is_active' => true,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
            }
        }

        DisplaySetting::flushSettingsCache();

        return [
            'success' => ! empty($detected['sender_ref']),
            'errors' => $errors,
            'detected' => $detected,
        ];
    }
}
