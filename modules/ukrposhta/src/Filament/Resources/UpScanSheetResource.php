<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UpScanSheetResource\Pages;
use App\Models\UpScanSheet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UpScanSheetResource extends Resource
{
    use \App\Filament\Concerns\GatedResource;

    protected static ?string $model = UpScanSheet::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $navigationLabel = 'УкрПошта: реєстри';

    protected static ?string $modelLabel = 'Реєстр УП';

    protected static ?string $pluralModelLabel = 'Реєстри УП';

    protected static ?int $navigationSort = 170;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Назва')->required()->maxLength(200),
            Forms\Components\TextInput::make('uuid')->label('UkrPoshta UUID')->disabled()->dehydrated(),
            Forms\Components\TextInput::make('shipments_count')->label('Кількість ТТН')->disabled()->dehydrated(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable(),
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UkrPoshta UUID')
                    ->fontFamily('mono')
                    ->limit(20)
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('shipments_count')->label('ТТН')->badge()->color('info'),
                Tables\Columns\TextColumn::make('printed_at')->label('Надруковано')->dateTime('d.m.Y H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('Створено')->dateTime('d.m.Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('print_form')
                    ->label('Друк PDF')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn (UpScanSheet $r) => (bool) $r->uuid)
                    ->action(function (UpScanSheet $r) {
                        $svc = app(\App\Services\UkrPoshtaEcomService::class);
                        $result = $svc->downloadRegistryForm($r->uuid);

                        if (! $result['success']) {
                            Notification::make()->title('Не вдалося отримати PDF')
                                ->body(implode('; ', $result['errors']))->danger()->send();

                            return null;
                        }

                        $r->update(['printed_at' => now()]);

                        return response()->streamDownload(
                            fn () => print($result['pdf']),
                            "registry-{$r->id}.pdf",
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUpScanSheets::route('/'),
            'edit' => Pages\EditUpScanSheet::route('/{record}/edit'),
        ];
    }
}
