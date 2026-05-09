<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UpShipmentResource\Pages;
use App\Models\Order;
use App\Models\UpCity;
use App\Models\UpPostOffice;
use App\Models\UpShipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UpShipmentResource extends Resource
{
    use \App\Filament\Concerns\RequiresModule;

    protected static string $moduleKey = 'ukrposhta';

    protected static ?string $model = UpShipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Доставка та оплата';

    protected static ?string $navigationLabel = 'ТТН (УкрПошта)';

    protected static ?string $modelLabel = 'УП ТТН';

    protected static ?string $pluralModelLabel = 'УП ТТН';

    protected static ?int $navigationSort = 11;

    public static function getNavigationBadge(): ?string
    {
        $count = UpShipment::whereNull('ttn')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Замовлення')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Замовлення')
                        ->options(Order::query()->latest()->limit(50)->pluck('id', 'id'))
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $s) => Order::where('id', 'like', "{$s}%")
                            ->limit(20)->pluck('id', 'id')->toArray())
                        ->getOptionLabelUsing(fn ($v) => "#{$v}"),
                ]),

            Forms\Components\Section::make('Отримувач')
                ->schema([
                    Forms\Components\TextInput::make('recipient_name')->label('ПІБ')->required()->maxLength(200),
                    Forms\Components\TextInput::make('recipient_phone')->label('Телефон')->required()->maxLength(30),
                    Forms\Components\TextInput::make('recipient_email')->label('Email')->email()->maxLength(100),

                    Forms\Components\Select::make('service_type')
                        ->label('Тип доставки')
                        ->options([
                            'branch' => 'Відділення',
                            'courier' => 'Курʼєр (адресна)',
                            'express' => 'Експрес',
                        ])
                        ->default('branch')
                        ->required()
                        ->live(),

                    Forms\Components\Select::make('recipient_city_id')
                        ->label('Місто')
                        ->searchable()
                        ->required()
                        ->live()
                        ->getSearchResultsUsing(fn (string $s) => UpCity::query()
                            ->search($s)
                            ->orderByDesc('population')
                            ->limit(30)
                            ->get()
                            ->mapWithKeys(fn ($c) => [$c->id => trim(($c->city_type_ua ?? '').' '.$c->name_ua).
                                ($c->district_ua ? ', '.$c->district_ua : '')])
                            ->toArray())
                        ->getOptionLabelUsing(function ($value) {
                            $c = UpCity::find((int) $value);

                            return $c ? trim(($c->city_type_ua ?? '').' '.$c->name_ua) : (string) $value;
                        })
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $c = UpCity::find((int) $state);
                                $set('recipient_city_name', $c ? trim(($c->city_type_ua ?? '').' '.$c->name_ua) : '');
                                $set('recipient_branch_id', null);
                            }
                        }),

                    Forms\Components\Hidden::make('recipient_city_name'),

                    Forms\Components\Select::make('recipient_branch_id')
                        ->label('Відділення')
                        ->searchable()
                        ->visible(fn (Forms\Get $get) => $get('service_type') === 'branch' && $get('recipient_city_id'))
                        ->getSearchResultsUsing(function (string $s, Forms\Get $get) {
                            $cityId = (int) $get('recipient_city_id');
                            if (! $cityId) {
                                return [];
                            }

                            return UpPostOffice::forCity($cityId)
                                ->active()
                                ->where(function ($q) use ($s) {
                                    $q->where('postcode', 'like', "{$s}%")
                                        ->orWhere('address', 'like', "%{$s}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($b) => [$b->id => "{$b->postcode} — ".str($b->address)->limit(60)])
                                ->toArray();
                        })
                        ->options(function (Forms\Get $get) {
                            $cityId = (int) $get('recipient_city_id');
                            if (! $cityId) {
                                return [];
                            }

                            return UpPostOffice::forCity($cityId)->active()->limit(100)
                                ->get()->mapWithKeys(fn ($b) => [$b->id => "{$b->postcode} — ".str($b->address)->limit(60)])
                                ->toArray();
                        })
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                $b = UpPostOffice::find((int) $state);
                                $set('recipient_branch_address', $b?->address);
                                $set('recipient_postcode', $b?->postcode);
                            }
                        }),

                    Forms\Components\Hidden::make('recipient_branch_address'),
                    Forms\Components\Hidden::make('recipient_postcode'),

                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('recipient_street')->label('Вулиця'),
                        Forms\Components\TextInput::make('recipient_building')->label('Будинок'),
                        Forms\Components\TextInput::make('recipient_apartment')->label('Квартира'),
                    ])->columns(3)
                        ->visible(fn (Forms\Get $get) => $get('service_type') === 'courier'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Параметри')
                ->schema([
                    Forms\Components\TextInput::make('weight')->label('Вага (кг)')->numeric()->step(0.1)->minValue(0.1)->required()->default(0.5),
                    Forms\Components\TextInput::make('declared_value')->label('Оголошена вартість')->numeric()->prefix('₴')->required(),
                    Forms\Components\TextInput::make('cod_amount')->label('Сума накладеного платежу')->numeric()->prefix('₴'),
                    Forms\Components\TextInput::make('shipping_cost')->label('Вартість доставки')->numeric()->prefix('₴'),
                    Forms\Components\Textarea::make('description')->label('Опис вмісту')->rows(2)->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('TTN та статус')
                ->description('TTN-номер вводиться вручну (отриманий у кабінеті УкрПошти my.ukrposhta.ua)')
                ->schema([
                    Forms\Components\TextInput::make('ttn')
                        ->label('Номер ТТН')
                        ->placeholder('Наприклад: 0500001234567')
                        ->maxLength(30),
                    Forms\Components\Select::make('status')
                        ->label('Статус')
                        ->options([
                            UpShipment::STATUS_NEW => 'Нова',
                            UpShipment::STATUS_SENT => 'Відправлено',
                            UpShipment::STATUS_IN_TRANSIT => 'В дорозі',
                            UpShipment::STATUS_ARRIVED => 'Прибула у відділення',
                            UpShipment::STATUS_DELIVERED => 'Доставлено',
                            UpShipment::STATUS_RETURNED => 'Повернуто',
                        ])
                        ->default(UpShipment::STATUS_NEW)
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('ttn')
                    ->label('ТТН')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Замовлення')
                    ->formatStateUsing(fn ($state) => $state ? "#{$state}" : '—')
                    ->url(fn (UpShipment $r) => $r->order_id ? \App\Filament\Resources\OrderResource::getUrl('edit', ['record' => $r->order_id]) : null),
                Tables\Columns\TextColumn::make('recipient_name')->label('Отримувач')->searchable()->limit(25),
                Tables\Columns\TextColumn::make('recipient_city_name')->label('Місто')->limit(20),
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (UpShipment $r) => match ($r->status) {
                        UpShipment::STATUS_DELIVERED => 'success',
                        UpShipment::STATUS_IN_TRANSIT, UpShipment::STATUS_SENT => 'info',
                        UpShipment::STATUS_RETURNED => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('declared_value')->label('Сума')->money('UAH'),
                Tables\Columns\TextColumn::make('created_at')->label('Створено')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        UpShipment::STATUS_NEW => 'Нова',
                        UpShipment::STATUS_SENT => 'Відправлено',
                        UpShipment::STATUS_IN_TRANSIT => 'В дорозі',
                        UpShipment::STATUS_DELIVERED => 'Доставлено',
                        UpShipment::STATUS_RETURNED => 'Повернуто',
                    ]),
                Tables\Filters\TernaryFilter::make('has_ttn')
                    ->label('TTN присвоєно')
                    ->placeholder('Усі')
                    ->trueLabel('Має ТТН')
                    ->falseLabel('Без ТТН (ввести вручну)')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('ttn'),
                        false: fn ($q) => $q->whereNull('ttn'),
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('track_url')
                    ->label('Відстеження УП')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (UpShipment $r) => $r->getTrackingUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (UpShipment $r) => (bool) $r->ttn),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('add_to_registry')
                    ->label('Додати до реєстру')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Створимо новий реєстр УкрПошти і додамо обрані ТТН (тільки ті, що вже мають номер).')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                        $candidates = $records->filter(fn (UpShipment $r) => ! empty($r->ttn));
                        if ($candidates->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Немає ТТН з номером для реєстру')
                                ->warning()->send();

                            return;
                        }

                        $svc = app(\App\Services\UkrPoshtaEcomService::class);
                        $reg = $svc->createRegistry();
                        if (! ($reg['success'] ?? false) || empty($reg['response']['uuid'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Не вдалося створити реєстр')
                                ->body(implode('; ', $reg['errors'] ?? []))
                                ->danger()->send();

                            return;
                        }

                        $registryUuid = $reg['response']['uuid'];
                        $added = 0;
                        $failed = 0;
                        foreach ($candidates as $sh) {
                            // Resolve shipment uuid — barcode is stored in ttn; recover uuid from latest API log
                            $shipmentUuid = self::resolveShipmentUuid($sh);
                            if (! $shipmentUuid) {
                                $failed++;
                                continue;
                            }
                            $r = $svc->addShipmentToRegistry($registryUuid, $shipmentUuid);
                            $r['success'] ? $added++ : $failed++;
                        }

                        \App\Models\UpScanSheet::create([
                            'uuid' => $registryUuid,
                            'name' => $reg['response']['name'] ?? 'Реєстр '.now()->format('d.m.Y H:i'),
                            'shipments_count' => $added,
                            'shipment_uuids' => $candidates->pluck('id')->toArray(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title("Реєстр створено")
                            ->body("Додано {$added} ТТН, помилок: {$failed}")
                            ->color($failed === 0 ? 'success' : 'warning')
                            ->duration(15000)
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUpShipments::route('/'),
            'create' => Pages\CreateUpShipment::route('/create'),
            'edit' => Pages\EditUpShipment::route('/{record}/edit'),
        ];
    }

    /**
     * Try to recover the eCom shipment uuid for a UpShipment by inspecting the
     * latest successful createShipment API log. ttn (barcode) alone cannot be
     * used for registry endpoints — they require the uuid.
     */
    public static function resolveShipmentUuid(UpShipment $sh): ?string
    {
        $log = \App\Models\ShippingApiLog::query()
            ->where('provider', 'ukrposhta')
            ->where('endpoint_method', 'POST /shipments')
            ->where('success', true)
            ->orderByDesc('id')
            ->take(50)
            ->get();

        foreach ($log as $row) {
            $resp = is_array($row->response_payload) ? $row->response_payload : [];
            if (($resp['barcode'] ?? null) === $sh->ttn && ! empty($resp['uuid'])) {
                return $resp['uuid'];
            }
        }

        return null;
    }
}
