<?php

namespace App\Livewire\Shipping;

use App\Models\UpCity;
use App\Models\UpPostOffice;
use App\Services\UkrPoshtaApiService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * UkrPoshta city/branch selector for the checkout page. Mirrors NovaPoshtaSelector
 * but reads from local up_cities/up_post_offices cache (synced via up:sync-references).
 * If a city is not yet cached, falls back to live Address Classifier API.
 */
class UkrPoshtaSelector extends Component
{
    public string $deliveryType = 'branch'; // branch | courier

    // City state
    public string $citySearch = '';

    public ?int $cityId = null;

    public string $cityName = '';

    public array $citySuggestions = [];

    public bool $cityLoading = false;

    // Branch (post office) state
    public ?int $branchId = null;

    public string $branchName = '';

    public string $branchSearch = '';

    public array $branchSuggestions = [];

    public array $allBranches = [];

    public bool $branchLoading = false;

    /** Branch type filter: '' = всі, 'ПВ' = пункт видачі, 'ВПЗ' = виїзне */
    public string $branchTypeFilter = '';

    // Courier address
    public string $street = '';

    public ?int $streetId = null;

    public array $streetSuggestions = [];

    public bool $streetLoading = false;

    public string $building = '';

    public string $apartment = '';

    public function updatedStreet(): void
    {
        $this->streetId = null;

        if (! $this->cityId || mb_strlen($this->street) < 2) {
            $this->streetSuggestions = [];

            return;
        }

        $this->streetLoading = true;

        try {
            $rows = app(UkrPoshtaApiService::class)
                ->getStreets($this->street, $this->cityId);

            $this->streetSuggestions = collect($rows)
                ->take(15)
                ->map(fn ($r) => [
                    'id' => (int) ($r->STREET_ID ?? 0),
                    'name' => trim(($r->SHORTSTREETTYPE_UA ?? '').' '.($r->STREET_UA ?? '')),
                ])
                ->filter(fn ($x) => $x['id'] > 0 && $x['name'] !== '')
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning('UP selector street search: '.$e->getMessage());
            $this->streetSuggestions = [];
        } finally {
            $this->streetLoading = false;
        }
    }

    public function selectStreetByIndex(int $index): void
    {
        $picked = $this->streetSuggestions[$index] ?? null;
        if (! $picked) {
            return;
        }

        $this->street = $picked['name'];
        $this->streetId = $picked['id'];
        $this->streetSuggestions = [];

        $this->dispatchSelection();
    }

    public function updatedDeliveryType(): void
    {
        // Reset opposite-mode data and notify checkout so shippingMethod stays in sync.
        if ($this->deliveryType === 'courier') {
            $this->resetBranchData();
        } else {
            $this->street = '';
            $this->streetId = null;
            $this->building = '';
            $this->apartment = '';
        }

        $this->dispatchSelection();
    }

    public function updatedBuilding(): void
    {
        $this->dispatchSelection();
    }

    public function updatedApartment(): void
    {
        $this->dispatchSelection();
    }

    public function updatedCitySearch(): void
    {
        if ($this->cityId) {
            $this->cityId = null;
            $this->cityName = '';
            $this->resetBranchData();
        }

        if (mb_strlen($this->citySearch) < 2) {
            $this->citySuggestions = [];

            return;
        }

        $this->cityLoading = true;

        try {
            // Try local cache first (fast)
            $cached = UpCity::query()
                ->search($this->citySearch)
                ->orderByDesc('population')
                ->limit(15)
                ->get(['id', 'name_ua', 'district_ua', 'city_type_ua', 'postcode']);

            if ($cached->isEmpty()) {
                // Fallback: live API
                $rows = app(UkrPoshtaApiService::class)->getCities($this->citySearch);
                $this->citySuggestions = collect($rows)
                    ->take(15)
                    ->map(fn ($r) => [
                        'id' => (int) ($r->CITY_ID ?? 0),
                        'name' => trim(($r->SHORTCITYTYPE_UA ?? '').' '.($r->CITY_UA ?? '')),
                        'district' => $r->DISTRICT_UA ?? '',
                        'postcode' => $r->POSTCODE ?? '',
                    ])
                    ->filter(fn ($x) => $x['id'] > 0)
                    ->values()
                    ->toArray();
            } else {
                $this->citySuggestions = $cached->map(fn (UpCity $c) => [
                    'id' => $c->id,
                    'name' => trim(($c->city_type_ua ?? '').' '.$c->name_ua),
                    'district' => $c->district_ua ?? '',
                    'postcode' => $c->postcode ?? '',
                ])->toArray();
            }
        } catch (\Throwable $e) {
            Log::warning('UP selector city search: '.$e->getMessage());
            $this->citySuggestions = [];
        } finally {
            $this->cityLoading = false;
        }
    }

    public function selectCityByIndex(int $index): void
    {
        $picked = $this->citySuggestions[$index] ?? null;
        if (! $picked) {
            return;
        }

        $this->cityId = (int) $picked['id'];
        $this->cityName = $picked['name'];
        $this->citySearch = $picked['name'];
        $this->citySuggestions = [];

        $this->loadBranchesForCity();
    }

    public function updatedBranchSearch(): void
    {
        $this->refreshBranchSuggestions();
    }

    public function setBranchTypeFilter(string $type): void
    {
        $this->branchTypeFilter = $type;
        $this->refreshBranchSuggestions();
    }

    private function refreshBranchSuggestions(): void
    {
        if ($this->branchId) {
            $this->branchId = null;
            $this->branchName = '';
        }
        if (! $this->cityId) {
            $this->branchSuggestions = [];

            return;
        }

        $term = trim($this->branchSearch);
        $type = $this->branchTypeFilter;

        $this->branchSuggestions = collect($this->allBranches)
            ->when($type !== '', fn ($c) => $c->filter(fn ($b) => ($b['type'] ?? '') === $type))
            ->when($term !== '', fn ($c) => $c->filter(fn ($b) => str_contains(mb_strtolower($b['address']), mb_strtolower($term))
                || str_starts_with($b['postcode'], $term)))
            ->take(50)
            ->values()
            ->toArray();
    }

    public function selectBranchByIndex(int $index): void
    {
        $b = $this->branchSuggestions[$index] ?? null;
        if (! $b) {
            return;
        }
        $this->branchId = (int) $b['id'];
        $this->branchName = $b['address'];
        $this->branchSearch = $b['address'];
        $this->branchSuggestions = [];

        $this->dispatchSelection();
    }

    private function loadBranchesForCity(): void
    {
        $this->branchLoading = true;

        try {
            $cached = UpPostOffice::forCity($this->cityId)
                ->active()
                ->orderBy('postcode')
                ->limit(300)
                ->get(['id', 'postcode', 'address', 'type_acronym', 'type_long']);

            if ($cached->isEmpty()) {
                // Live fetch
                $rows = app(UkrPoshtaApiService::class)->getPostOffices($this->cityId);
                $this->allBranches = collect($rows)
                    ->map(fn ($r, $i) => [
                        'id' => (int) ($r->ID ?? $r->id ?? $i),
                        'postcode' => (string) ($r->POSTCODE ?? ''),
                        'address' => trim(($r->POSTCODE ?? '').' '.($r->ADDRESS ?? '')),
                        'type' => $r->TYPE_ACRONYM ?? null,
                    ])
                    ->filter(fn ($x) => $x['address'] !== '')
                    ->values()
                    ->toArray();
            } else {
                $this->allBranches = $cached->map(fn (UpPostOffice $b) => [
                    'id' => $b->id,
                    'postcode' => $b->postcode,
                    'address' => trim($b->postcode.' '.($b->address ?? '')),
                    'type' => $b->type_acronym,
                ])->toArray();
            }

            $this->branchSuggestions = array_slice($this->allBranches, 0, 50);
        } catch (\Throwable $e) {
            Log::warning('UP selector branch load: '.$e->getMessage());
            $this->allBranches = [];
            $this->branchSuggestions = [];
        } finally {
            $this->branchLoading = false;
        }
    }

    private function resetBranchData(): void
    {
        $this->branchId = null;
        $this->branchName = '';
        $this->branchSearch = '';
        $this->branchSuggestions = [];
        $this->allBranches = [];
    }

    /**
     * Notify checkout listener about the selection.
     */
    private function dispatchSelection(): void
    {
        $payload = [
            'provider' => 'ukrposhta',
            'method' => $this->deliveryType,
            'city_id' => $this->cityId,
            'city_name' => $this->cityName,
            'branch_id' => $this->branchId,
            'branch_name' => $this->branchName,
            'street' => $this->street,
            'street_id' => $this->streetId,
            'building' => $this->building,
            'apartment' => $this->apartment,
        ];
        $this->dispatch('ukrposhta-selected', $payload);
        session(['up_last_delivery' => $payload]);
    }

    public function mount(): void
    {
        $last = session('up_last_delivery');
        if (! is_array($last)) {
            return;
        }

        $this->deliveryType = $last['method'] ?? $this->deliveryType;
        $this->cityId = isset($last['city_id']) ? (int) $last['city_id'] : null;
        $this->cityName = (string) ($last['city_name'] ?? '');
        $this->citySearch = $this->cityName;

        if ($this->deliveryType === 'branch' && ! empty($last['branch_id'])) {
            $this->branchId = (int) $last['branch_id'];
            $this->branchName = (string) ($last['branch_name'] ?? '');
            $this->branchSearch = $this->branchName;
            if ($this->cityId) {
                $this->loadBranchesForCity();
            }
        }

        if ($this->deliveryType === 'courier') {
            $this->street = (string) ($last['street'] ?? '');
            $this->streetId = isset($last['street_id']) ? (int) $last['street_id'] : null;
            $this->building = (string) ($last['building'] ?? '');
            $this->apartment = (string) ($last['apartment'] ?? '');
        }
    }

    public function render()
    {
        return view('livewire.shipping.ukr-poshta-selector');
    }
}
