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
                'Система',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources($this->collectModuleResources())
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages(array_merge([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ThemeSettings::class,
                \App\Filament\Pages\ModuleSettings::class,
                \App\Filament\Pages\DemoCatalogGenerator::class,
            ], $this->collectModulePages()))
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
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
            ]);
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
    private function collectModuleClasses(string $manifestKey): array
    {
        $classes = [];
        foreach (\App\Support\ModuleDiscovery::manifests() as $name => $manifest) {
            if (! \App\Support\ModuleManager::for($name)->enabled()) {
                continue;
            }
            foreach ($manifest[$manifestKey] ?? [] as $class) {
                if (is_string($class) && class_exists($class)) {
                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }
}
