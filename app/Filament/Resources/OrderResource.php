<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Продажі';
    protected static ?string $navigationLabel = 'Замовлення';

    protected static ?string $modelLabel = 'Замовлення';

    protected static ?string $pluralModelLabel = 'Замовлення';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', '0')
            ->orWhere('payment_status', 'pending')
            ->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', '0')->count();
        return $count > 5 ? 'danger' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Group::make()
                    ->schema([
                        // Основна колонка (ліва)

                        Forms\Components\Section::make('Інформація про клієнта')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(auth()->id())
                                    ->label('Менеджер')
                                    ->prefixIcon('heroicon-o-user-circle'),
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Імʼя')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyFirstName')
                                            ->icon('heroicon-o-clipboard')
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($wire.first_name)'])
                                    ),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Прізвище')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyLastName')
                                            ->icon('heroicon-o-clipboard')
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($wire.last_name)'])
                                    ),
                                Forms\Components\TextInput::make('middle_name')
                                    ->label('По батькові')
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyMiddleName')
                                            ->icon('heroicon-o-clipboard')
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($wire.middle_name)'])
                                    ),
                                Forms\Components\TextInput::make('email')
                                    ->label('Електронна пошта')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('Не обов\'язково')
                                    ->live(onBlur: true)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyEmail')
                                            ->icon('heroicon-o-clipboard')
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($wire.email)'])
                                    ),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->default('')
                                    ->live(onBlur: true)
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyPhone')
                                            ->icon('heroicon-o-clipboard')
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($wire.phone)'])
                                    ),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Forms\Components\Section::make('Доставка')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Forms\Components\Select::make('shipping_provider')
                                    ->label('Служба доставки')
                                    ->options(function () {
                                        return \App\Models\ShippingProvider::active()
                                            ->get()
                                            ->pluck('name', 'code')
                                            ->toArray();
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $set('shipping_method', null);
                                    }),
                                Forms\Components\Select::make('shipping_method')
                                    ->label('Спосіб доставки')
                                    ->options(function (Forms\Get $get) {
                                        $providerCode = $get('shipping_provider');
                                        if (! $providerCode) {
                                            return [];
                                        }

                                        return \App\Models\ShippingMethod::byProvider($providerCode)
                                            ->active()
                                            ->get()
                                            ->pluck('name', 'method_code')
                                            ->toArray();
                                    })
                                    ->live(),
                                Forms\Components\TextInput::make('shipping_cost')
                                    ->label('Вартість доставки')
                                    ->numeric()
                                    ->prefix('₴')
                                    ->minValue(0)
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        static::updateOrderTotal($get, $set);
                                    })
                                    ->required()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyShippingCost')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                            ])
                            ->columns(3)
                            ->collapsible(),

                        Forms\Components\Section::make('Деталі доставки')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                // Поля для Нової Пошти - Відділення
                                Forms\Components\Select::make('np_city')
                                    ->label('Місто (НП)')
                                    ->searchable()
                                    ->placeholder('Почніть вводити назву міста...')
                                    ->dehydrated(true)
                                    ->required(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'warehouse')
                                    ->options(function ($state, $record) {
                                        // First priority: use the current state value
                                        if ($state) {
                                            try {
                                                $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                                $city = $provider->getCityByRef($state);
                                                if ($city) {
                                                    return [$state => $city['name']];
                                                }
                                            } catch (\Exception $e) {
                                                \Log::error('Cannot get city for state: '.$e->getMessage());
                                            }
                                        }

                                        // Second priority: use the record's shipping data
                                        if ($record && $record->shipping_data) {
                                            // Перевіряємо чи це вже масив чи JSON строка
                                            if (is_array($record->shipping_data)) {
                                                $shippingData = $record->shipping_data;
                                            } else {
                                                $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            }

                                            // If we have a city_ref, try to get the name
                                            if (isset($shippingData['city_ref'])) {
                                                $cityName = $shippingData['city_name'] ?? $shippingData['city'] ?? null;

                                                // If city name is not in shipping_data, try to get it from API
                                                if (! $cityName) {
                                                    try {
                                                        $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                                        $city = $provider->getCityByRef($shippingData['city_ref']);
                                                        if ($city && isset($city['name'])) {
                                                            $cityName = $city['name'];
                                                        }
                                                    } catch (\Exception $e) {
                                                        \Log::error('Cannot get city name for options: '.$e->getMessage());
                                                    }
                                                }

                                                // Return the option with city name or user-friendly placeholder
                                                if ($cityName) {
                                                    return [$shippingData['city_ref'] => $cityName];
                                                } else {
                                                    return [$shippingData['city_ref'] => 'Місто (потребує оновлення)'];
                                                }
                                            }
                                        }

                                        return [];
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'ref')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        // Очистити відділення при зміні міста
                                        $set('np_warehouse', null);
                                    })
                                    ->preload()
                                    ->getOptionLabelUsing(function ($value) {
                                        // Отримати назву міста тільки через API
                                        if (! $value) {
                                            return '';
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;

                                            // Try to get city by ref directly
                                            $city = $provider->getCityByRef($value);

                                            if ($city && isset($city['name'])) {
                                                return $city['name'];
                                            }

                                            \Log::warning('City not found for ref: '.$value);
                                        } catch (\Exception $e) {
                                            \Log::error('Cannot get city label for ref '.$value.': '.$e->getMessage());
                                        }

                                        // Return user-friendly fallback instead of UUID
                                        return 'Місто (потребує оновлення)';
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'warehouse')
                                    ->afterStateHydrated(function ($component, $state) {
                                        \Log::info('City field hydrated with state: '.($state ?? 'NULL'));
                                        if ($state) {
                                            // Ensure the option is available
                                            try {
                                                $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                                $city = $provider->getCityByRef($state);
                                                if ($city) {
                                                    $component->options([$state => $city['name']]);
                                                    \Log::info('Set city option: '.$city['name']);
                                                }
                                            } catch (\Exception $e) {
                                                \Log::error('Cannot set city option: '.$e->getMessage());
                                            }
                                        }
                                    })
                                    ->dehydrateStateUsing(fn ($state) => $state),
                                Forms\Components\Select::make('np_warehouse')
                                    ->label('Відділення')
                                    ->searchable()
                                    ->placeholder('Спочатку виберіть місто')
                                    ->dehydrated(true)
                                    ->required(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'warehouse' && $get('np_city'))
                                    ->options(function ($record, Forms\Get $get) {
                                        $cityRef = $get('np_city');

                                        // Якщо немає вибраного міста, не показувати нічого
                                        if (! $cityRef) {
                                            return [];
                                        }

                                        // Показувати поточне відділення ТІЛЬКИ якщо воно відповідає вибраному місту
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            // Перевірити чи поточне відділення належить до вибраного міста
                                            if (isset($shippingData['warehouse_ref'], $shippingData['warehouse'])
                                                && isset($shippingData['city_ref'])
                                                && $shippingData['city_ref'] === $cityRef) {
                                                // Включити поточне відділення в список опцій
                                                $currentWarehouse = [$shippingData['warehouse_ref'] => $shippingData['warehouse']];
                                            }
                                        }

                                        // Завантажити всі відділення для вибраного міста
                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $warehouses = $provider->getWarehouses($cityRef);

                                            $warehouseOptions = $warehouses->take(50)->mapWithKeys(function ($warehouse) {
                                                return [$warehouse['ref'] => "№{$warehouse['number']} - {$warehouse['description']}"];
                                            })->toArray();

                                            // Об'єднати з поточним відділенням якщо воно є
                                            if (isset($currentWarehouse)) {
                                                return array_merge($currentWarehouse, $warehouseOptions);
                                            }

                                            return $warehouseOptions;
                                        } catch (\Exception $e) {
                                            return isset($currentWarehouse) ? $currentWarehouse : [];
                                        }
                                    })
                                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                                        $cityRef = $get('np_city');
                                        if (! $cityRef) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $warehouses = $provider->getWarehouses($cityRef);

                                            if ($search && strlen($search) >= 1) {
                                                $warehouses = $warehouses->filter(function ($warehouse) use ($search) {
                                                    return str_contains(strtolower($warehouse['description']), strtolower($search)) ||
                                                           str_contains(strtolower($warehouse['number']), strtolower($search));
                                                });
                                            }

                                            return $warehouses->take(50)->mapWithKeys(function ($warehouse) {
                                                return [$warehouse['ref'] => "№{$warehouse['number']} - {$warehouse['description']}"];
                                            })->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->preload()
                                    ->getOptionLabelUsing(function ($value, Forms\Get $get) {
                                        // Отримати назву відділення для відображення замість ID
                                        if (! $value) {
                                            return '';
                                        }

                                        $cityRef = $get('np_city');
                                        if (! $cityRef) {
                                            return $value;
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $warehouses = $provider->getWarehouses($cityRef);
                                            $warehouse = $warehouses->firstWhere('ref', $value);
                                            if ($warehouse) {
                                                return "№{$warehouse['number']} - {$warehouse['description']}";
                                            }
                                        } catch (\Exception $e) {
                                            // Якщо помилка API, повернути value
                                        }

                                        return $value;
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'warehouse')
                                    ->dehydrateStateUsing(fn ($state) => $state),

                                // Поля для Нової Пошти - Поштомат
                                Forms\Components\Select::make('np_postomat_city')
                                    ->label('Місто (поштомат)')
                                    ->searchable()
                                    ->options(function ($record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['postomat_city_ref']) && isset($shippingData['city'])) {
                                                return [$shippingData['postomat_city_ref'] => $shippingData['city']];
                                            }
                                        }

                                        return [];
                                    })
                                    ->preload()
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'ref')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $set('np_postomat', null);
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'postomat'),
                                Forms\Components\Select::make('np_postomat')
                                    ->label('Поштомат')
                                    ->searchable()
                                    ->options(function ($record, Forms\Get $get) {
                                        // Pre-load current postomat if editing existing record
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['postomat_ref'], $shippingData['postomat'])) {
                                                return [$shippingData['postomat_ref'] => $shippingData['postomat']];
                                            }
                                        }

                                        // Load all postomats for the selected city
                                        $cityRef = $get('np_postomat_city');
                                        if ($cityRef) {
                                            try {
                                                $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                                $warehouses = $provider->getWarehouses($cityRef);

                                                // Фільтруємо поштомати
                                                $postomats = $warehouses->filter(function ($warehouse) {
                                                    $description = mb_strtolower($warehouse['description'] ?? '');

                                                    return str_contains($description, 'поштомат') || str_contains($description, 'poshtomat');
                                                });

                                                return $postomats->take(50)->mapWithKeys(function ($postomat) {
                                                    return [$postomat['ref'] => $postomat['description']];
                                                })->toArray();
                                            } catch (\Exception $e) {
                                                return [];
                                            }
                                        }

                                        return [];
                                    })
                                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                                        $cityRef = $get('np_postomat_city');
                                        if (! $cityRef) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $warehouses = $provider->getWarehouses($cityRef);

                                            // Фільтруємо поштомати
                                            $postomats = $warehouses->filter(function ($warehouse) {
                                                $description = mb_strtolower($warehouse['description'] ?? '');

                                                return str_contains($description, 'поштомат') || str_contains($description, 'poshtomat');
                                            });

                                            if ($search) {
                                                $postomats = $postomats->filter(function ($warehouse) use ($search) {
                                                    return str_contains(mb_strtolower($warehouse['description']), mb_strtolower($search));
                                                });
                                            }

                                            return $postomats->take(50)->mapWithKeys(function ($postomat) {
                                                return [$postomat['ref'] => $postomat['description']];
                                            })->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->preload()
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'postomat'),

                                // Поля для Нової Пошти - Кур'єр
                                Forms\Components\Select::make('np_courier_city')
                                    ->label('Місто доставки')
                                    ->searchable()
                                    ->options(function ($record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            // For courier, look for courier_city_ref
                                            $cityRef = $shippingData['courier_city_ref'] ?? null;
                                            if ($cityRef) {
                                                $cityName = $shippingData['city_name'] ?? $shippingData['city'] ?? null;

                                                // If city name is not in shipping_data, try to get it from API
                                                if (! $cityName || strlen($cityName) === 36) { // UUID is 36 chars
                                                    try {
                                                        $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                                        $city = $provider->getCityByRef($cityRef);
                                                        if ($city && isset($city['name'])) {
                                                            $cityName = $city['name'];
                                                        }
                                                    } catch (\Exception $e) {
                                                        \Log::error('Cannot get city name for courier city: '.$e->getMessage());
                                                    }
                                                }

                                                return [$cityRef => $cityName ?: 'Місто (потребує оновлення)'];
                                            }
                                        }

                                        return [];
                                    })
                                    ->preload()
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'ref')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            $cityRef = $shippingData['courier_city_ref'] ?? null;
                                            if ($cityRef) {
                                                $cityName = $shippingData['city'] ?? null;
                                                if (! $cityName) {
                                                    try {
                                                        $provider = new \App\Services\Shipping\NovaPoshtaProvider;
                                                        $city = $provider->getCityByRef($cityRef);
                                                        if ($city && isset($city['name'])) {
                                                            $cityName = $city['name'];
                                                        }
                                                    } catch (\Exception $e) {
                                                        $cityName = 'Місто (потребує оновлення)';
                                                    }
                                                }
                                                $component->state($cityRef);
                                                $component->options([$cityRef => $cityName]);
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'courier'),
                                Forms\Components\Select::make('np_courier_street')
                                    ->label('Вулиця')
                                    ->searchable()
                                    ->live()
                                    ->allowHtml()
                                    ->helperText(fn (Forms\Get $get) => $get('np_courier_city') ? null : 'Спочатку оберіть місто')
                                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                                        $cityRef = $get('np_courier_city');
                                        if (! $cityRef || mb_strlen($search) < 2) {
                                            return [];
                                        }
                                        try {
                                            $resp = app(\App\Services\NovaPoshtaApiService::class)
                                                ->getStreets($cityRef, $search);
                                            $items = $resp['data'] ?? [];

                                            return collect($items)->take(20)->mapWithKeys(function ($s) {
                                                $label = trim(($s['StreetsType'] ?? '').' '.($s['Description'] ?? ''));

                                                return [$label => $label];
                                            })->toArray();
                                        } catch (\Throwable $e) {
                                            return [];
                                        }
                                    })
                                    ->getOptionLabelUsing(fn ($value) => $value)
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'courier'),
                                Forms\Components\TextInput::make('np_courier_building')
                                    ->label('Номер будинку')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyBuilding')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\TextInput::make('np_courier_apartment')
                                    ->label('Квартира')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'novaposhta' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyApartment')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),

                                // Поля для УкрПошти - з автодоповненням
                                Forms\Components\Select::make('ukr_city_id')
                                    ->label('Місто (УкрПошта)')
                                    ->searchable()
                                    ->options([])
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }
                                        try {
                                            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $set('ukr_branch_id', null);
                                        // Update shipping_city when city changes
                                        if ($state) {
                                            try {
                                                $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                                $cities = $provider->getCities();
                                                $city = $cities->firstWhere('id', $state);
                                                if ($city) {
                                                    $set('shipping_city', $city['name']);
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore errors
                                            }
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                $component->state($shippingData['city_id']);
                                                $component->options([$shippingData['city_id'] => $shippingData['city_name']]);
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'ukrposhta'),
                                Forms\Components\Select::make('ukr_branch_id')
                                    ->label('Відділення УкрПошти')
                                    ->searchable()
                                    ->placeholder('Спочатку виберіть місто')
                                    ->options(function (Forms\Get $get, $record) {
                                        $cityId = $get('ukr_city_id');

                                        // If editing existing record, load from shipping_data first
                                        if ($record && $record->shipping_data && ! $cityId) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'])) {
                                                $cityId = $shippingData['city_id'];
                                            }
                                        }

                                        if (! $cityId) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                            $warehouses = $provider->getWarehouses($cityId);

                                            return $warehouses->take(100)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                                        $cityId = $get('ukr_city_id');
                                        if (! $cityId) {
                                            return [];
                                        }
                                        try {
                                            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                            $warehouses = $provider->getWarehouses($cityId);
                                            if ($search && strlen($search) >= 1) {
                                                $warehouses = $warehouses->filter(function ($warehouse) use ($search) {
                                                    return str_contains(mb_strtolower($warehouse['name']), mb_strtolower($search));
                                                });
                                            }

                                            return $warehouses->take(50)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->preload()
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        // Update shipping_post_office when branch changes
                                        if ($state) {
                                            try {
                                                $cityId = $get('ukr_city_id');
                                                $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                                $warehouses = $provider->getWarehouses($cityId);
                                                $branch = $warehouses->firstWhere('id', $state);
                                                if ($branch) {
                                                    $set('shipping_post_office', $branch['name']);
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore errors
                                            }
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['branch_id'])) {
                                                $component->state($shippingData['branch_id']);
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'ukrposhta' && $get('shipping_method') === 'branch'),
                                // Hidden fields to store actual data
                                Forms\Components\Hidden::make('shipping_city'),
                                Forms\Components\Hidden::make('shipping_post_office'),

                                // Поля для УкрПошти - кур'єр
                                Forms\Components\Select::make('ukr_courier_city_id')
                                    ->label('Місто (УкрПошта кур\'єр)')
                                    ->searchable()
                                    ->options(function ($record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                return [$shippingData['city_id'] => $shippingData['city_name']];
                                            }
                                        }

                                        return [];
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }
                                        try {
                                            $provider = new \App\Services\Shipping\UkrPoshtaProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                $component->state($shippingData['city_id']);
                                                $component->options([$shippingData['city_id'] => $shippingData['city_name']]);
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'ukrposhta' && $get('shipping_method') === 'courier'),
                                Forms\Components\Select::make('ukr_courier_street')
                                    ->label('Вулиця (УкрПошта)')
                                    ->searchable()
                                    ->helperText(fn (Forms\Get $get) => $get('ukr_courier_city_id') ? null : 'Спочатку оберіть місто')
                                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                                        $cityId = $get('ukr_courier_city_id');
                                        if (! $cityId || mb_strlen($search) < 2) {
                                            return [];
                                        }
                                        try {
                                            $items = app(\App\Services\UkrPoshtaApiService::class)
                                                ->getStreets($search, (int) $cityId);

                                            return collect($items)->take(20)->mapWithKeys(function ($s) {
                                                $type = $s['SHORTSTREETTYPE_UA'] ?? $s['STREETTYPE_UA'] ?? '';
                                                $name = $s['STREET_UA'] ?? '';
                                                $label = trim($type.' '.$name);

                                                return [$label => $label];
                                            })->filter(fn ($v, $k) => $k !== '')->toArray();
                                        } catch (\Throwable $e) {
                                            return [];
                                        }
                                    })
                                    ->getOptionLabelUsing(fn ($value) => $value)
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'ukrposhta' && $get('shipping_method') === 'courier'),
                                Forms\Components\TextInput::make('ukr_courier_building')
                                    ->label('Будинок (УкрПошта)')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'ukrposhta' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyUkrBuilding')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\TextInput::make('ukr_courier_apartment')
                                    ->label('Квартира (УкрПошта)')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'ukrposhta' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyUkrApartment')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),

                                // Поля для Rozetka Delivery
                                Forms\Components\Select::make('rozetka_city_id')
                                    ->label('Місто (Rozetka)')
                                    ->searchable()
                                    ->options(function ($record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                return [$shippingData['city_id'] => $shippingData['city_name']];
                                            }
                                        }

                                        return [];
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }
                                        try {
                                            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                $component->state($shippingData['city_id']);
                                                $component->options([$shippingData['city_id'] => $shippingData['city_name']]);
                                            }
                                        }
                                    })
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $set('rozetka_pickup_point_id', null);
                                        // Update shipping_city when city changes
                                        if ($state) {
                                            try {
                                                $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                                $cities = $provider->getCities();
                                                $city = $cities->firstWhere('id', $state);
                                                if ($city) {
                                                    $set('shipping_city', $city['name']);
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore errors
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'rozetka'),
                                Forms\Components\Select::make('rozetka_pickup_point_id')
                                    ->label('Пункт видачі (Rozetka)')
                                    ->searchable()
                                    ->placeholder('Спочатку виберіть місто')
                                    ->options(function (Forms\Get $get, $record) {
                                        $cityId = $get('rozetka_city_id');

                                        // If editing existing record, load from shipping_data first
                                        if ($record && $record->shipping_data && ! $cityId) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'])) {
                                                $cityId = $shippingData['city_id'];
                                            }
                                        }

                                        if (! $cityId) {
                                            return [];
                                        }

                                        try {
                                            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                            $points = $provider->getPickupPoints($cityId);

                                            return $points->take(100)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->getSearchResultsUsing(function (string $search, Forms\Get $get) {
                                        $cityId = $get('rozetka_city_id');
                                        if (! $cityId) {
                                            return [];
                                        }
                                        try {
                                            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                            $points = $provider->getPickupPoints($cityId);
                                            if ($search && strlen($search) >= 1) {
                                                $points = $points->filter(function ($point) use ($search) {
                                                    return str_contains(mb_strtolower($point['name']), mb_strtolower($search)) ||
                                                           str_contains(mb_strtolower($point['address']), mb_strtolower($search));
                                                });
                                            }

                                            return $points->take(50)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->preload()
                                    ->live()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        // Update shipping_post_office when pickup point changes
                                        if ($state) {
                                            try {
                                                $cityId = $get('rozetka_city_id');
                                                $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                                $points = $provider->getPickupPoints($cityId);
                                                $point = $points->firstWhere('id', $state);
                                                if ($point) {
                                                    $set('shipping_post_office', $point['name']);
                                                }
                                            } catch (\Exception $e) {
                                                // Ignore errors
                                            }
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['pickup_point_id'])) {
                                                $component->state($shippingData['pickup_point_id']);
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'rozetka' && $get('shipping_method') === 'pickup_point'),

                                // Поля для Rozetka кур'єр
                                Forms\Components\Select::make('rozetka_courier_city_id')
                                    ->label('Місто (Rozetka кур\'єр)')
                                    ->searchable()
                                    ->options(function ($record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                return [$shippingData['city_id'] => $shippingData['city_name']];
                                            }
                                        }

                                        return [];
                                    })
                                    ->getSearchResultsUsing(function (string $search) {
                                        if (strlen($search) < 2) {
                                            return [];
                                        }
                                        try {
                                            $provider = new \App\Services\Shipping\RozetkaDeliveryProvider;
                                            $cities = $provider->getCities($search);

                                            return $cities->take(20)->pluck('name', 'id')->toArray();
                                        } catch (\Exception $e) {
                                            return [];
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                                        if ($record && $record->shipping_data) {
                                            $shippingData = is_array($record->shipping_data) ? $record->shipping_data : json_decode($record->shipping_data, true);
                                            if (isset($shippingData['city_id'], $shippingData['city_name'])) {
                                                $component->state($shippingData['city_id']);
                                                $component->options([$shippingData['city_id'] => $shippingData['city_name']]);
                                            }
                                        }
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'rozetka' && $get('shipping_method') === 'courier'),
                                Forms\Components\TextInput::make('rozetka_courier_street')
                                    ->label('Вулиця (Rozetka)')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'rozetka' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyRozetkaStreet')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\TextInput::make('rozetka_courier_building')
                                    ->label('Будинок (Rozetka)')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'rozetka' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyRozetkaBuilding')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\TextInput::make('rozetka_courier_apartment')
                                    ->label('Квартира (Rozetka)')
                                    ->visible(fn (Forms\Get $get) => $get('shipping_provider') === 'rozetka' && $get('shipping_method') === 'courier')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyRozetkaApartment')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),

                                // Скрите поле для JSON даних (для збереження)
                                Forms\Components\Hidden::make('shipping_data')
                                    ->dehydrated(false),
                            ])
                            ->columns(2)
                            ->visible(fn (Forms\Get $get) => $get('shipping_provider') && $get('shipping_provider') !== 'pickup')
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        // Сайдбар (права колонка)
                        Forms\Components\Section::make('Оплата')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Forms\Components\Select::make('payment_method')
                                    ->label('Спосіб оплати')
                                    ->options(function () {
                                        return \App\Models\PaymentGatewaySettings::where('is_active', true)
                                            ->get()
                                            ->pluck('name', 'code')
                                            ->toArray();
                                    })
                                    ->default('cash')
                                    ->live()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyPaymentMethod')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\Select::make('payment_status')
                                    ->label('Статус оплати')
                                    ->options([
                                        'pending' => 'Очікує оплати',
                                        'processing' => 'Обробляється',
                                        'success' => 'Оплачено',
                                        'failed' => 'Не вдалося',
                                        'refunded' => 'Повернено',
                                    ])
                                    ->default('pending')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state === 'success') {
                                            $set('paid_at', now());
                                        } elseif ($state === 'pending') {
                                            $set('paid_at', null);
                                        }
                                    })
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyPaymentStatus')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\DateTimePicker::make('paid_at')
                                    ->label('Дата оплати')
                                    ->visible(fn (Forms\Get $get) => $get('payment_status') === 'success')
                                    ->displayFormat('d.m.Y H:i')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyPaidAt')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                            ])
                            ->columns(1),

                        Forms\Components\Section::make('Статус замовлення')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Статус замовлення')
                                    ->options(fn () => \App\Models\OrderStatus::options())
                                    ->required()
                                    ->default(fn () => \App\Models\OrderStatus::defaultKey())
                                    ->live()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyStatus')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                                Forms\Components\TextInput::make('total')
                                    ->label('Загальна сума')
                                    ->disabled()
                                    ->numeric()
                                    ->prefix('₴')
                                    ->default(0)
                                    ->extraInputAttributes(['class' => 'text-lg font-semibold'])
                                    ->helperText('Автоматично розраховується з товарів + доставка')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('copyTotal')
                                            ->icon('heroicon-o-clipboard')
                                            ->action(fn () => null)
                                            ->extraAttributes(['x-on:click' => 'navigator.clipboard.writeText($event.target.closest(\'.fi-fo-field-wrp\').querySelector(\'input, select, textarea\').value)'])
                                    ),
                            ])
                            ->columns(1),

                        Forms\Components\Section::make('Додаткова інформація')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Textarea::make('note')
                                    ->label('Примітка')
                                    ->rows(3)
                                    ->extraInputAttributes([
                                        'x-data' => '{ copy() { navigator.clipboard.writeText($el.value); } }',
                                        'x-on:dblclick' => 'copy()',
                                    ])
                                    ->hint('Подвійний клік для копіювання')
                                    ->hintIcon('heroicon-o-clipboard'),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('')
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-hashtag')
                    ->iconPosition('before')
                    ->color('primary')
                    ->copyable()
                    ->tooltip('Номер замовлення'),
                Tables\Columns\ImageColumn::make('product_thumbs')
                    ->label('Товари')
                    ->getStateUsing(function (Order $record): array {
                        $record->loadMissing('orderProducts.product.category');

                        // Known seed-placeholders that don't actually exist on disk.
                        $placeholderPaths = [
                            '/assets/img/default-product.jpg',
                            'assets/img/default-product.jpg',
                        ];

                        return $record->orderProducts
                            ->take(4)
                            ->map(function ($op) use ($placeholderPaths) {
                                // Prefer explicit op.image, then product.image — but ignore seed-placeholders.
                                $candidate = $op->image ?: ($op->product?->image ?? null);
                                if ($candidate && ! in_array($candidate, $placeholderPaths, true)) {
                                    return str_starts_with($candidate, 'http') || str_starts_with($candidate, '/')
                                        ? $candidate
                                        : '/'.ltrim($candidate, '/');
                                }
                                $catTitle = $op->product?->category?->title;
                                if (is_array($catTitle)) {
                                    $catTitle = $catTitle['uk'] ?? '';
                                }
                                $seed = $op->product_id ?: $op->id;
                                // guaranteedKind → завжди kind із реальним фото-пулом,
                                // тож resolve() віддасть webp, а не порожню SVG-монограму
                                // (як було, коли категорія не мапилась).
                                $kind = \App\Support\PartImage::guaranteedKind((string) $catTitle, $seed);
                                $title = $op->title ?? $op->product?->title ?? '';
                                if (is_array($title)) {
                                    $title = $title['uk'] ?? '';
                                }
                                return \App\Support\PartImage::resolve(
                                    explicit: null,
                                    kind: $kind,
                                    seed: $seed,
                                    title: (string) $title,
                                );
                            })
                            ->all();
                    })
                    ->stacked()
                    ->limit(4)
                    ->limitedRemainingText(isSeparate: true)
                    ->size(40)
                    ->extraImgAttributes(['class' => 'rounded-md ring-2 ring-white object-cover bg-gray-50'])
                    ->checkFileExistence(false)
                    // Назви товарів при наведенні на картинки.
                    ->tooltip(function (Order $record): ?string {
                        $record->loadMissing('orderProducts');
                        $lines = $record->orderProducts->map(function ($op) {
                            $title = $op->title ?? $op->product?->title ?? 'Товар';
                            if (is_array($title)) {
                                $title = $title['uk'] ?? reset($title) ?: 'Товар';
                            }
                            $qty = (int) ($op->quantity ?? 1);

                            return $qty > 1 ? "{$title} × {$qty}" : (string) $title;
                        })->all();

                        return ! empty($lines) ? implode("\n", $lines) : null;
                    }),
                // Менеджер — toggleable hidden by default (не обов'язково за вимогою)
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->label('Менеджер')
                    ->circular()
                    ->size(30)
                    ->defaultImageUrl(asset('assets/images/default-avatar.svg'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Імʼя менеджера')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Імʼя')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(function (Order $record): string {
                        $parts = [];
                        if ($record->last_name) {
                            $parts[] = $record->last_name;
                        }
                        if ($record->first_name) {
                            $parts[] = mb_substr($record->first_name, 0, 1, 'UTF-8').'.';
                        }
                        if ($record->middle_name) {
                            $parts[] = mb_substr($record->middle_name, 0, 1, 'UTF-8').'.';
                        }
                        return implode(' ', $parts) ?: 'Не вказано';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search) {
                            $query->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('last_name', $direction)
                            ->orderBy('first_name', $direction);
                    }),
                Tables\Columns\TextColumn::make('email')
                    ->label('Пошта')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shipping_provider')
                    ->label('Доставка')
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return 'Не вказано';
                        }

                        $provider = \App\Models\ShippingProvider::where('code', $state)->first();

                        return $provider ? $provider->name : $state;
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'novaposhta' => 'primary',
                        'pickup' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Спосіб')
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return 'Не вказано';
                        }

                        $method = \App\Models\ShippingMethod::where('method_code', $state)->first();

                        return $method ? $method->name : $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (?string $state): string => \App\Models\OrderStatus::options()[$state] ?? ($state ?: 'Невідомо'))
                    ->color(fn (?string $state): string => \App\Models\OrderStatus::colors()[$state] ?? 'gray')
                    ->icon(fn (?string $state): ?string => \App\Models\OrderStatus::icons()[$state] ?? null),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Оплата')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Очікує',
                        'processing' => 'Обробляється',
                        'success' => 'Оплачено',
                        'failed' => 'Не вдалося',
                        'refunded' => 'Повернено',
                        default => 'Не вказано',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'success' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Спосіб оплати')
                    ->formatStateUsing(function (?string $state): string {
                        if (! $state) {
                            return 'Не вказано';
                        }

                        $gateway = \App\Models\PaymentGatewaySettings::where('code', $state)->first();

                        return $gateway ? $gateway->name : $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total')
                    ->label('Сума')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ' ').' грн')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('np_no_ttn')
                    ->label('NP без ТТН')
                    ->query(fn ($q) => $q->where('shipping_provider', 'novaposhta')
                        ->whereDoesntHave('npShipments')),
                Tables\Filters\Filter::make('np_with_ttn')
                    ->label('NP з ТТН')
                    ->query(fn ($q) => $q->where('shipping_provider', 'novaposhta')
                        ->whereHas('npShipments', fn ($s) => $s->whereNotNull('ttn'))),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус замовлення')
                    ->options(fn () => \App\Models\OrderStatus::options()),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Статус оплати')
                    ->options([
                        'pending' => 'Очікує оплати',
                        'processing' => 'Обробляється',
                        'success' => 'Оплачено',
                        'failed' => 'Не вдалося',
                        'refunded' => 'Повернено',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Спосіб оплати')
                    ->options(function () {
                        return \App\Models\PaymentGatewaySettings::where('is_active', true)
                            ->get()
                            ->pluck('name', 'code')
                            ->toArray();
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('gazuShipping')
                    ->label('')
                    ->icon('heroicon-o-truck')
                    ->size('lg')
                    ->color('primary')
                    ->tooltip('Деталі доставки (GAZU)')
                    ->visible(fn (Order $record) => ! empty($record->shipping_city_ref) || ! empty($record->shipping_data))
                    ->modalHeading(fn (Order $record) => 'Доставка для замовлення #'.$record->id)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Закрити')
                    ->modalContent(function (Order $record) {
                        $sd = is_array($record->shipping_data) ? $record->shipping_data : (json_decode($record->shipping_data ?? '[]', true) ?: []);
                        $methodLabels = ['novaposhta' => 'Нова Пошта', 'ukrposhta' => 'УкрПошта', 'pickup' => 'Самовивіз'];
                        $typeLabels = ['branch' => 'Відділення', 'postomat' => 'Поштомат', 'np_courier' => 'Курʼєр НП'];
                        $rows = [
                            'Метод' => $methodLabels[$record->shipping_method] ?? $record->shipping_method,
                            'Тип НП' => $typeLabels[$record->shipping_warehouse_type] ?? $record->shipping_warehouse_type,
                            'Місто' => $record->shipping_city,
                            'City Ref' => $record->shipping_city_ref ? substr($record->shipping_city_ref, 0, 12).'…' : null,
                            'Відділення' => $record->shipping_warehouse,
                            'Wh Ref' => $record->shipping_warehouse_ref ? substr($record->shipping_warehouse_ref, 0, 12).'…' : null,
                            'Адреса' => $record->shipping_address,
                            'Поштовий індекс' => $record->shipping_postcode,
                        ];
                        if (! empty($sd['street'])) {
                            $rows['Вулиця'] = $sd['street'];
                        }
                        if (! empty($sd['house'])) {
                            $rows['Будинок'] = $sd['house'];
                        }
                        if (! empty($sd['apartment'])) {
                            $rows['Квартира'] = $sd['apartment'];
                        }
                        if (! empty($sd['floor'])) {
                            $rows['Поверх'] = $sd['floor'];
                        }
                        if (isset($sd['has_elevator'])) {
                            $rows['Ліфт'] = $sd['has_elevator'] ? 'Так' : 'Ні';
                        }
                        if (! empty($sd['preferred_date'])) {
                            $rows['Бажана дата'] = $sd['preferred_date'];
                        }
                        if (! empty($sd['preferred_time'])) {
                            $rows['Бажаний час'] = $sd['preferred_time'];
                        }
                        $html = '<div class="space-y-2 text-sm">';
                        foreach ($rows as $k => $v) {
                            if ($v !== null && $v !== '') {
                                $html .= '<div class="flex gap-3 border-b border-gray-100 dark:border-gray-700 pb-1.5"><dt class="font-semibold w-36 text-gray-600 dark:text-gray-400">'.e($k).'</dt><dd class="text-gray-900 dark:text-gray-100 flex-1">'.e($v).'</dd></div>';
                            }
                        }
                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    }),
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->size('lg')
                    ->tooltip('Перегляд'),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-o-pencil')
                    ->size('lg')
                    ->tooltip('Змінити'),
                Tables\Actions\Action::make('splitShipments')
                    ->label('')
                    ->icon('heroicon-o-rectangle-stack')
                    ->size('lg')
                    ->color('warning')
                    ->tooltip('Створити ТТН для кожного складу')
                    ->visible(function (Order $record) {
                        $whCount = $record->orderProducts()
                            ->whereNotNull('warehouse_id')
                            ->distinct('warehouse_id')
                            ->count('warehouse_id');
                        return $whCount > 1;
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $record) => 'Розбити замовлення №'.$record->id.' на ТТН')
                    ->modalDescription('Згенерувати окрему ТТН для кожного складу. ТТН створюються як draft — без виклику API НП. Зможете відредагувати та відправити вручну.')
                    ->form([
                        \Filament\Forms\Components\Select::make('provider')
                            ->label('Перевізник')
                            ->options(['nova' => 'Нова Пошта', 'ukr' => 'УкрПошта'])
                            ->default('nova')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        $splitter = app(\App\Services\Shipping\OrderShipmentSplitter::class);
                        $shipments = $splitter->split($record, $data['provider'] ?? 'nova');

                        \Filament\Notifications\Notification::make()
                            ->title($shipments->count() > 0
                                ? 'Створено '.$shipments->count().' draft-ТТН'
                                : 'ТТН не створено (усі склади вже мають shipment)')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('markCompleted')
                    ->label('')
                    ->icon('heroicon-o-check-circle')
                    ->size('lg')
                    ->color('success')
                    ->tooltip('Позначити виконаним')
                    ->action(fn (Order $record) => $record->update(['status' => 'completed']))
                    ->visible(fn (Order $record) => $record->status !== 'completed'),
                Tables\Actions\Action::make('markPaid')
                    ->label('')
                    ->icon('heroicon-o-credit-card')
                    ->size('lg')
                    ->color('success')
                    ->tooltip('Позначити оплаченим')
                    ->action(fn (Order $record) => $record->update([
                        'payment_status' => 'success',
                        'paid_at' => now(),
                    ]))
                    ->visible(fn (Order $record) => $record->payment_status !== 'success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markCompleted')
                        ->label('Позначити виконаними')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'completed'])),
                    Tables\Actions\BulkAction::make('markPaid')
                        ->label('Позначити оплаченими')
                        ->icon('heroicon-o-credit-card')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update([
                            'payment_status' => 'success',
                            'paid_at' => now(),
                        ])),
                    Tables\Actions\BulkAction::make('markPending')
                        ->label('Позначити очікуючими оплати')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update([
                            'payment_status' => 'pending',
                            'paid_at' => null,
                        ])),

                    Tables\Actions\BulkAction::make('createNpShipments')
                        ->label('Створити ТТН Нової Пошти')
                        ->icon('heroicon-o-truck')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Масове створення ТТН')
                        ->modalDescription('Для кожного обраного замовлення з доставкою Нова Пошта буде створено локальний запис ТТН та зроблено спробу запиту до NP API.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $created = 0;
                            $skipped = 0;
                            $errors = 0;

                            foreach ($records as $order) {
                                if ($order->shipping_provider !== 'novaposhta') {
                                    $skipped++;
                                    continue;
                                }
                                if (\App\Models\NpShipment::where('order_id', $order->id)->exists()) {
                                    $skipped++;
                                    continue;
                                }

                                $shippingData = is_array($order->shipping_data)
                                    ? $order->shipping_data
                                    : (json_decode($order->shipping_data ?? '{}', true) ?: []);

                                try {
                                    $shipment = \App\Models\NpShipment::create([
                                        'order_id' => $order->id,
                                        'status' => \App\Models\NpShipment::STATUS_NEW,
                                        'recipient_name' => trim("{$order->first_name} {$order->last_name}"),
                                        'recipient_phone' => $order->phone,
                                        'recipient_email' => $order->email,
                                        'recipient_city_ref' => $shippingData['city_ref'] ?? '',
                                        'recipient_city_name' => $shippingData['city_name'] ?? '',
                                        'recipient_warehouse_ref' => $shippingData['warehouse_ref'] ?? null,
                                        'recipient_edrpou' => $shippingData['edrpou'] ?? null,
                                        'recipient_company_name' => $shippingData['company_name'] ?? null,
                                        'recipient_contact_name' => $shippingData['contact_person'] ?? null,
                                        'service_type' => (($order->shipping_method ?? '') === 'courier') ? 'WarehouseDoors' : 'WarehouseWarehouse',
                                        'cargo_type' => 'Parcel',
                                        'weight' => method_exists($order, 'calculateTotalWeight') ? $order->calculateTotalWeight() : 0.5,
                                        'cost' => $order->total,
                                        'declared_cost' => $order->total,
                                        'shipping_cost' => $order->shipping_cost ?? 0,
                                        'payer_type' => 'Recipient',
                                        'payment_method' => in_array($order->payment_method, ['cash', 'cash_on_delivery']) ? 'Cash' : 'NonCash',
                                        'description' => "Замовлення #{$order->id}",
                                    ]);
                                    $created++;
                                } catch (\Throwable $e) {
                                    \Log::error("Bulk TTN create failed for order #{$order->id}: ".$e->getMessage());
                                    $errors++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Bulk TTN створення завершено')
                                ->body("Створено: {$created}, пропущено: {$skipped}, помилок: {$errors}")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderProductsRelationManager::class,
            RelationManagers\NpShipmentsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['orderProducts:id,order_id,image']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    protected static function updateOrderTotal(Forms\Get $get, Forms\Set $set): void
    {
        $products = $get('../../orderProducts') ?? [];
        $shippingCost = (float) ($get('../../shipping_cost') ?? 0);

        $productsTotal = 0;
        foreach ($products as $product) {
            $quantity = (int) ($product['quantity'] ?? 0);
            $price = (float) ($product['price'] ?? 0);
            $productsTotal += $quantity * $price;
        }

        $total = $productsTotal + $shippingCost;
        $set('../../total', $total);
    }
}
