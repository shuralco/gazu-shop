<?php

namespace App\Filament\Pages;

use App\Models\HomepageModule;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomepageBuilder extends Page implements HasForms
{
    use \App\Filament\Concerns\GatedPage;

    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationLabel = 'Конструктор головної';

    protected static ?string $title = 'Конструктор головної сторінки';

    protected static ?string $navigationGroup = 'Контент і SEO';

    protected static ?int $navigationSort = 80;

    protected static string $view = 'filament.pages.homepage-builder';

    public bool $showAddModal = false;

    public bool $showEditModal = false;

    public ?int $editingModuleId = null;

    public ?string $editingModuleType = null;

    public array $moduleSettings = [];

    public ?string $moduleTitle = null;

    public function getModules()
    {
        return HomepageModule::ordered()->get();
    }

    public function getAvailableTypes(): array
    {
        return HomepageModule::getAvailableTypes();
    }

    public function addModule(string $type): void
    {
        $types = HomepageModule::getAvailableTypes();
        if (!isset($types[$type])) return;

        $maxOrder = HomepageModule::max('sort_order') ?? 0;

        HomepageModule::create([
            'type' => $type,
            'title' => $types[$type]['name'],
            'settings' => HomepageModule::getDefaultSettings($type),
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        $this->showAddModal = false;
        $this->clearHomepageCache();

        Notification::make()
            ->title('Модуль додано')
            ->success()
            ->send();
    }

    public function toggleModule(int $id): void
    {
        $module = HomepageModule::findOrFail($id);
        $module->update(['is_active' => !$module->is_active]);
        $this->clearHomepageCache();

        Notification::make()
            ->title($module->is_active ? 'Модуль увімкнено' : 'Модуль вимкнено')
            ->success()
            ->send();
    }

    public function deleteModule(int $id): void
    {
        HomepageModule::findOrFail($id)->delete();
        $this->clearHomepageCache();

        Notification::make()
            ->title('Модуль видалено')
            ->success()
            ->send();
    }

    public function moveUp(int $id): void
    {
        $module = HomepageModule::findOrFail($id);
        $prev = HomepageModule::where('sort_order', '<', $module->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) {
            $tempOrder = $module->sort_order;
            $module->update(['sort_order' => $prev->sort_order]);
            $prev->update(['sort_order' => $tempOrder]);
            $this->clearHomepageCache();
        }
    }

    public function moveDown(int $id): void
    {
        $module = HomepageModule::findOrFail($id);
        $next = HomepageModule::where('sort_order', '>', $module->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $tempOrder = $module->sort_order;
            $module->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $tempOrder]);
            $this->clearHomepageCache();
        }
    }

    public function openEditModal(int $id): void
    {
        $module = HomepageModule::findOrFail($id);
        $this->editingModuleId = $module->id;
        $this->editingModuleType = $module->type;
        $this->moduleTitle = $module->title;
        $this->moduleSettings = $module->settings ?? [];

        // Serialize slides array to JSON for the textarea editor
        if ($module->type === 'hero_slider' && isset($this->moduleSettings['slides'])) {
            $this->moduleSettings['slides_json'] = json_encode($this->moduleSettings['slides'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $this->showEditModal = true;
    }

    public function saveModuleSettings(): void
    {
        if (!$this->editingModuleId) return;

        $module = HomepageModule::findOrFail($this->editingModuleId);

        $settings = $this->moduleSettings;

        // Parse slides_json back to slides array for hero_slider
        if ($module->type === 'hero_slider' && !empty($settings['slides_json'])) {
            $decoded = json_decode($settings['slides_json'], true);
            if (is_array($decoded)) {
                $settings['slides'] = $decoded;
            }
            unset($settings['slides_json']);
        }

        $module->update([
            'title' => $this->moduleTitle,
            'settings' => $settings,
        ]);

        $this->showEditModal = false;
        $this->editingModuleId = null;
        $this->editingModuleType = null;
        $this->moduleSettings = [];
        $this->moduleTitle = null;
        $this->clearHomepageCache();

        Notification::make()
            ->title('Налаштування збережено')
            ->success()
            ->send();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingModuleId = null;
        $this->editingModuleType = null;
        $this->moduleSettings = [];
        $this->moduleTitle = null;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function openAddModal(): void
    {
        $this->showAddModal = true;
    }

    protected function clearHomepageCache(): void
    {
        cache()->forget('home_page_data_v2');
        cache()->forget('homepage_modules');
    }
}
