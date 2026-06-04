<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessPresetResource\Pages;
use App\Models\AccessPreset;
use App\Support\Access\AccessControl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Manage access-rights presets (lightweight RBAC roles) + their per-section
 * permission matrix. is_admin-only (never preset-gated — managing access is a
 * super-admin job). Section keys = Resource/Page class basenames.
 */
class AccessPresetResource extends Resource
{
    protected static ?string $model = AccessPreset::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Система';
    protected static ?string $navigationLabel = 'Доступ і ролі';
    protected static ?string $modelLabel = 'пресет доступу';
    protected static ?string $pluralModelLabel = 'Пресети доступу';
    protected static ?int $navigationSort = 30;

    /** Only super-admins manage presets. NOT gated by the preset system itself. */
    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Пресет')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Назва')->required()->maxLength(60)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Forms\Set $set, ?AccessPreset $record) {
                            if (! $record) {
                                $set('key', Str::slug((string) $state, '_'));
                            }
                        }),
                    Forms\Components\TextInput::make('key')
                        ->label('Ключ')->required()->maxLength(50)
                        ->helperText('Унікальний slug. Не змінюйте після створення.')
                        ->unique(ignoreRecord: true)
                        ->disabledOn('edit'),
                    Forms\Components\TextInput::make('description')
                        ->label('Опис')->maxLength(180)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Права доступу')
                ->description('Які розділи адмінки доступні цьому пресету та з якими діями. is_admin бачить усе незалежно від пресета.')
                ->schema(static::permissionMatrix()),
        ]);
    }

    /** Build collapsible permission matrix grouped by nav group. */
    protected static function permissionMatrix(): array
    {
        $abilities = ['view' => 'Перегляд', 'create' => 'Створення', 'update' => 'Редагування', 'delete' => 'Видалення'];
        $schema = [];

        foreach (collect(AccessControl::sections())->groupBy('group') as $groupName => $items) {
            $rows = [];
            foreach ($items as $it) {
                $key = $it['section'];
                $cells = [
                    Forms\Components\Placeholder::make("__lbl_{$key}")
                        ->hiddenLabel()->content($it['label'])->columnSpan(2),
                ];
                foreach ($abilities as $ab => $abLabel) {
                    if (in_array($ab, $it['abilities'], true)) {
                        $cells[] = Forms\Components\Toggle::make("permissions.{$key}.{$ab}")
                            ->label($abLabel)->inline(false);
                    } else {
                        $cells[] = Forms\Components\Placeholder::make("__sp_{$key}_{$ab}")
                            ->hiddenLabel()->content('—');
                    }
                }
                $rows[] = Forms\Components\Grid::make(6)->schema($cells);
            }

            $schema[] = Forms\Components\Section::make((string) $groupName)
                ->schema($rows)
                ->collapsible()
                ->collapsed()
                ->compact();
        }

        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Назва')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('key')->label('Ключ')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('description')->label('Опис')->limit(50)->toggleable(),
                Tables\Columns\IconColumn::make('is_system')->label('Системний')->boolean(),
                Tables\Columns\TextColumn::make('users_count')->label('Користувачів')->counts('users')->badge(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()->excludeAttributes(['key'])
                    ->beforeReplicaSaved(function (AccessPreset $replica) {
                        $replica->key = $replica->key.'_copy';
                        $replica->name = $replica->name.' (копія)';
                        $replica->is_system = false;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (AccessPreset $r) => $r->is_system),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessPresets::route('/'),
            'create' => Pages\CreateAccessPreset::route('/create'),
            'edit' => Pages\EditAccessPreset::route('/{record}/edit'),
        ];
    }
}
