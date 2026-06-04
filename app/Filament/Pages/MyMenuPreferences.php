<?php

namespace App\Filament\Pages;

use App\Support\Access\AccessControl;
use App\Support\Access\NavPreferences;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Per-user menu customization — each user chooses which sidebar items to show
 * or hide for THEMSELVES. Cosmetic only (does not change access). Available to
 * any panel user (NOT gated by access preset).
 */
class MyMenuPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Налаштування';
    protected static ?string $navigationLabel = 'Моє меню';
    protected static ?string $title = 'Налаштування мого меню';
    protected static ?int $navigationSort = 100;
    protected static string $view = 'filament.pages.my-menu-preferences';

    public ?array $data = [];

    /** Available to every panel user (no preset gate). */
    public static function canAccess(): bool
    {
        return (bool) auth()->user();
    }

    public function mount(): void
    {
        $hidden = NavPreferences::hiddenFor(auth()->user());
        $visible = [];
        foreach ($this->accessibleSections() as $s) {
            $visible[$s['section']] = ! in_array($s['section'], $hidden, true);
        }
        $this->form->fill(['visible' => $visible]);
    }

    public function form(Form $form): Form
    {
        $schema = [];
        foreach (collect($this->accessibleSections())->groupBy('group') as $group => $items) {
            $toggles = [];
            foreach ($items as $s) {
                $toggles[] = Forms\Components\Toggle::make("visible.{$s['section']}")
                    ->label($s['label'])
                    ->inline(false)
                    ->default(true);
            }
            $schema[] = Forms\Components\Section::make((string) $group)
                ->schema($toggles)
                ->columns(2)
                ->collapsible();
        }

        return $form->schema($schema)->statePath('data');
    }

    public function save(): void
    {
        $visible = (array) ($this->form->getState()['visible'] ?? []);
        $hidden = [];
        foreach ($this->accessibleSections() as $s) {
            if (empty($visible[$s['section']])) {
                $hidden[] = $s['section'];
            }
        }

        NavPreferences::setHidden(auth()->user(), $hidden);

        Notification::make()->title('Меню оновлено')->success()->send();

        // Reload so the sidebar rebuilds with the new visibility.
        $this->redirect(static::getUrl());
    }

    public function resetMenu(): void
    {
        NavPreferences::setHidden(auth()->user(), []);
        Notification::make()->title('Показано всі пункти')->success()->send();
        $this->redirect(static::getUrl());
    }

    /** Sections the current user can actually access (filtered by RBAC). */
    protected function accessibleSections(): array
    {
        return collect(AccessControl::sections())
            ->filter(fn ($s) => AccessControl::can($s['section'], 'view'))
            ->values()
            ->all();
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
