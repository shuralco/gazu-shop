<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('GAZU · Адміністрування')
            ->favicon(asset('admin-favicon.svg'))
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
            ])
            ->darkMode()
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Каталог',
                'Продажі',
                'Доставка та оплата',
                'Контент та SEO',
                'Аналітика',
                'Налаштування',
                'Система',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources($this->collectModuleResources())
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages(array_merge([
                // Лише справжні core-сторінки. ThemeSettings (theme_settings) та
                // DemoCatalogGenerator (auto_parts_seed) — module-owned, реєструються
                // через collectModulePages() гейтнуто по enabled(). Хардкод тут робив
                // їх видимими навіть при вимкненому модулі + кидав би помилку при
                // видаленні модуля (fragile cross-namespace coupling).
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ModuleSettings::class,
            ], $this->collectModulePages()))
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets(array_merge([
                Widgets\AccountWidget::class,
            ], $this->collectModuleWidgets()))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \Filament\SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['uk', 'en']),
            ])
            // Inject JS that auto-recovers from stale Livewire snapshots.
            // If Livewire requests a component name that no longer exists
            // server-side (deleted widget, disabled module), force a full
            // page reload so the browser drops the snapshot.
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn () => <<<'HTML'
                    <script>
                    document.addEventListener('livewire:init', () => {
                        Livewire.hook('request', ({ fail }) => {
                            fail(({ status, content, preventDefault }) => {
                                if (status === 500 && content && content.includes('ComponentNotFoundException')) {
                                    preventDefault();
                                    console.warn('[livewire] component not found server-side — reloading');
                                    window.location.reload();
                                }
                            });
                        });
                    });
                    </script>
                HTML
            );
    }

    /**
     * Filament resources declared by modules/* /module.json — only for modules
     * currently enabled. This is how modular Filament resources opt-in to
     * the admin panel without being in app_path('Filament/Resources').
     *
     * @return array<int, class-string>
     */
    private function collectModuleResources(): array
    {
        return $this->collectModuleClasses('filament_resources');
    }

    /**
     * @return array<int, class-string>
     */
    private function collectModulePages(): array
    {
        return $this->collectModuleClasses('filament_pages');
    }

    /**
     * @return array<int, class-string>
     */
    private function collectModuleWidgets(): array
    {
        return $this->collectModuleClasses('filament_widgets');
    }

    /**
     * @return array<int, class-string>
     */
    private function collectModuleClasses(string $manifestKey): array
    {
        $classes = [];
        foreach (\App\Support\ModuleDiscovery::manifests() as $name => $manifest) {
            if (! \App\Support\ModuleManager::for($name)->enabled()) {
                continue;
            }
            foreach ($manifest[$manifestKey] ?? [] as $class) {
                // Defensive: only include if class actually resolves.
                // Prevents Filament panel boot from crashing on broken
                // manifests / partially-removed module files.
                if (! is_string($class) || ! class_exists($class)) {
                    continue;
                }
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
