<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Filter;
use App\Models\FilterGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class FiltersRelationManager extends RelationManager
{
    protected static string $relationship = 'filters';

    protected static ?string $title = 'Характеристики';
    protected static ?string $icon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $modelLabel = 'Характеристика';

    protected static ?string $pluralModelLabel = 'Характеристики';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('filter_group_id')
                    ->label('Група характеристик')
                    ->options(FilterGroup::where('is_active', true)->orderBy('sort_order')->pluck('title', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('title')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->label('Активна')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Порядок сортування')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('filterGroup.title')
                    ->label('Група')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Значення')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->defaultSort('filters.filter_group_id')
            ->filters([
                Tables\Filters\SelectFilter::make('filter_group_id')
                    ->label('Група')
                    ->options(FilterGroup::pluck('title', 'id')),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Додати існуючу характеристику')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Оберіть характеристику')
                            ->options(function () {
                                return FilterGroup::where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->mapWithKeys(function ($group) {
                                        $filters = $group->filters()
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->get()
                                            ->mapWithKeys(fn ($filter) => [
                                                $filter->id => $group->title.': '.$filter->title,
                                            ]);

                                        return $filters->toArray();
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $filter = Filter::find($data['recordId']);
                        if ($filter) {
                            $data['filter_group_id'] = $filter->filter_group_id;
                        }

                        return $data;
                    }),

                Tables\Actions\Action::make('create_filter')
                    ->label('Створити нову характеристику')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('filter_group_id')
                            ->label('Група характеристик')
                            ->options(FilterGroup::where('is_active', true)->orderBy('sort_order')->pluck('title', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('title')
                            ->label('Назва')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортування')
                            ->numeric()
                            ->default(0),
                    ])
                    ->action(function (array $data): void {
                        // Create the filter
                        $filter = Filter::create([
                            'title' => $data['title'],
                            'filter_group_id' => $data['filter_group_id'],
                            'is_active' => $data['is_active'] ?? true,
                            'sort_order' => $data['sort_order'] ?? 0,
                        ]);

                        // Attach to product
                        $this->ownerRecord->filters()->attach($filter->id, [
                            'filter_group_id' => $filter->filter_group_id,
                        ]);
                    })
                    ->modalHeading('Створити нову характеристику')
                    ->modalButton('Створити'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Видалити'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Видалити обрані'),
                ]),
            ])
            ->emptyStateHeading('Характеристики не додано')
            ->emptyStateDescription('Додайте характеристики для цього товару')
            ->emptyStateActions([
                Tables\Actions\AttachAction::make()
                    ->label('Додати характеристику'),
            ]);
    }
}
