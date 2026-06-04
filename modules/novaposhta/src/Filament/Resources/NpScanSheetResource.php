<?php

namespace App\Filament\Resources;

use App\Models\NpScanSheet;
use App\Models\NpShipment;
use App\Services\NovaPoshtaApiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class NpScanSheetResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = NpScanSheet::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Склад і доставка';
    protected static ?string $navigationLabel = 'Нова Пошта: реєстри';
    protected static ?string $modelLabel = 'Реєстр';
    protected static ?string $pluralModelLabel = 'Реєстри ТТН';
    protected static ?int $navigationSort = 120;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Реєстр')
                ->schema([
                    Forms\Components\DatePicker::make('date')
                        ->label('Дата відправлення')
                        ->default(now())
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->label('Статус')
                        ->options([
                            NpScanSheet::STATUS_DRAFT => 'Чернетка',
                            NpScanSheet::STATUS_PRINTED => 'Роздруковано',
                            NpScanSheet::STATUS_HANDED_OVER => 'Передано НП',
                        ])
                        ->default(NpScanSheet::STATUS_DRAFT)
                        ->required(),
                    Forms\Components\TextInput::make('ref')->label('NP Ref')->disabled(),
                    Forms\Components\TextInput::make('number')->label('NP Номер')->disabled(),
                ])
                ->columns(2),

            Forms\Components\Section::make('ТТН в реєстрі')
                ->schema([
                    Forms\Components\Select::make('shipment_refs')
                        ->label('Накладні (Ref)')
                        ->multiple()
                        ->searchable()
                        ->options(function () {
                            return NpShipment::query()
                                ->whereNotNull('ttn')
                                ->whereNull('registry_ref')
                                ->limit(200)
                                ->pluck('ttn', 'ref')
                                ->toArray();
                        })
                        ->dehydrated(false)
                        ->helperText('Оберіть TTN, які увійдуть до реєстру'),
                ])
                ->visibleOn('create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')->label('Дата')->date()->sortable(),
                Tables\Columns\TextColumn::make('number')->label('№')->copyable(),
                Tables\Columns\TextColumn::make('shipments_count')->label('ТТН')->numeric(),
                Tables\Columns\TextColumn::make('total_weight')->label('Вага')->numeric(decimalPlaces: 2)->suffix(' кг'),
                Tables\Columns\TextColumn::make('total_cost')->label('Сума')->money('UAH'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray' => NpScanSheet::STATUS_DRAFT,
                        'warning' => NpScanSheet::STATUS_PRINTED,
                        'success' => NpScanSheet::STATUS_HANDED_OVER,
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        NpScanSheet::STATUS_DRAFT => 'Чернетка',
                        NpScanSheet::STATUS_PRINTED => 'Роздруковано',
                        NpScanSheet::STATUS_HANDED_OVER => 'Передано НП',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        NpScanSheet::STATUS_DRAFT => 'Чернетка',
                        NpScanSheet::STATUS_PRINTED => 'Роздруковано',
                        NpScanSheet::STATUS_HANDED_OVER => 'Передано НП',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('create_at_np')
                        ->label('Створити у НП')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->visible(fn (NpScanSheet $r) => empty($r->ref) && $r->shipments_count > 0)
                        ->action(function (NpScanSheet $r) {
                            self::createAtNp($r);
                        }),
                    Tables\Actions\Action::make('print')
                        ->label('Друк PDF')
                        ->icon('heroicon-o-printer')
                        ->url(fn (NpScanSheet $r) => $r->ref
                            ? "https://my.novaposhta.ua/orders/printDocument/orders[]/{$r->ref}/type/pdf/apiKey/" . config('novaposhta.api_key')
                            : null
                        )
                        ->openUrlInNewTab()
                        ->visible(fn (NpScanSheet $r) => ! empty($r->ref)),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\NpScanSheetResource\Pages\ListNpScanSheets::route('/'),
            'create' => \App\Filament\Resources\NpScanSheetResource\Pages\CreateNpScanSheet::route('/create'),
            'edit' => \App\Filament\Resources\NpScanSheetResource\Pages\EditNpScanSheet::route('/{record}/edit'),
        ];
    }

    /**
     * Create scan-sheet at Nova Poshta side via API.
     */
    public static function createAtNp(NpScanSheet $sheet): void
    {
        $shipmentRefs = $sheet->shipments()->pluck('ref')->filter()->values()->toArray();
        if (empty($shipmentRefs)) {
            Notification::make()->warning()->title('Немає ТТН для реєстру')->send();
            return;
        }

        try {
            $api = app(NovaPoshtaApiService::class);
            $result = $api->createScanSheet($shipmentRefs, $sheet->date);

            if (! empty($result['success']) && ! empty($result['data'])) {
                $data = $result['data'][0];
                $sheet->update([
                    'ref' => $data['Ref'] ?? null,
                    'number' => $data['Number'] ?? null,
                ]);
                Notification::make()->success()->title('Реєстр створено в НП')->body("№ {$data['Number']}")->send();
            } else {
                $errors = implode('; ', $result['errors'] ?? ['Невідома помилка']);
                Notification::make()->danger()->title('Помилка створення реєстру')->body($errors)->send();
            }
        } catch (\Throwable $e) {
            Log::error('NP ScanSheet create failed: ' . $e->getMessage());
            Notification::make()->danger()->title('Помилка')->body($e->getMessage())->send();
        }
    }
}
