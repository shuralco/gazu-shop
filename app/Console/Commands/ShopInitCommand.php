<?php

namespace App\Console\Commands;

use App\Models\DisplaySetting;
use App\Models\MerchantWarehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Onboarding wizard for a fresh clone of SimpleShop.
 *
 * Sets up shop identity (name, contacts), creates the default warehouse
 * if missing, picks a theme, and shows next-step instructions.
 *
 * Usage:
 *   php artisan shop:init
 *   php artisan shop:init --non-interactive --shop-name="Auto Parts UA" \
 *       --warehouse-code=KYIV-1 --theme=auto-parts
 */
class ShopInitCommand extends Command
{
    protected $signature = 'shop:init
        {--non-interactive : skip prompts; use --shop-name etc. flags}
        {--shop-name=}
        {--shop-phone=}
        {--shop-email=}
        {--shop-city=}
        {--warehouse-code=MAIN-01}
        {--warehouse-name=Головний склад}
        {--theme=brutal : brutal | auto-parts}';

    protected $description = 'Bootstrap a fresh SimpleShop clone — shop identity, default warehouse, theme.';

    public function handle(): int
    {
        $this->info('=== SimpleShop init wizard ===');

        $interactive = ! $this->option('non-interactive');

        $shopName = $this->option('shop-name')
            ?: ($interactive ? $this->ask('Назва магазину', config('app.name', 'SimpleShop')) : 'SimpleShop');

        $shopPhone = $this->option('shop-phone')
            ?: ($interactive ? $this->ask('Телефон магазину', '+380000000000') : null);

        $shopEmail = $this->option('shop-email')
            ?: ($interactive ? $this->ask('Email магазину', 'info@example.com') : null);

        $shopCity = $this->option('shop-city')
            ?: ($interactive ? $this->ask('Місто магазину', 'Київ') : null);

        $warehouseCode = $this->option('warehouse-code') ?: 'MAIN-01';
        $warehouseName = $this->option('warehouse-name') ?: 'Головний склад';

        $availableThemes = collect(File::files(resource_path('css/tokens')))
            ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($n) => $n !== 'active')
            ->values()
            ->all();

        $theme = $this->option('theme') ?: 'brutal';
        if ($interactive && ! $this->option('theme')) {
            $theme = $this->choice('Тема (style)', $availableThemes, 'brutal');
        }
        if (! in_array($theme, $availableThemes, true)) {
            $this->error("Theme '{$theme}' not found. Available: ".implode(', ', $availableThemes));

            return self::FAILURE;
        }

        // === Apply changes ===
        $this->newLine();
        $this->info('Applying...');

        DisplaySetting::set('site_name', $shopName);
        $this->line("  ✓ site_name = {$shopName}");

        if ($shopPhone) {
            DisplaySetting::set('header_phone', $shopPhone);
            $this->line("  ✓ header_phone = {$shopPhone}");
        }
        if ($shopEmail) {
            DisplaySetting::set('header_email', $shopEmail);
            $this->line("  ✓ header_email = {$shopEmail}");
        }

        $warehouse = MerchantWarehouse::default();
        if (! $warehouse) {
            $warehouse = MerchantWarehouse::create([
                'code' => $warehouseCode,
                'name' => $warehouseName,
                'type' => MerchantWarehouse::TYPE_OWN,
                'country' => 'UA',
                'city' => $shopCity,
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 0,
            ]);
            $this->line("  ✓ Створено склад {$warehouse->code} ({$warehouse->name})");
        } else {
            $updates = [];
            if ($shopCity && empty($warehouse->city)) {
                $updates['city'] = $shopCity;
            }
            if ($updates) {
                $warehouse->update($updates);
            }
            $this->line("  ✓ Default warehouse already exists: {$warehouse->code}");
        }

        // Theme switch
        $appCss = resource_path('css/app.css');
        $css = File::get($appCss);
        $updated = preg_replace(
            '/@import\s+\'\.\/tokens\/[a-z0-9-]+\.css\';/i',
            "@import './tokens/{$theme}.css';",
            $css,
            1,
        );
        if ($updated !== $css) {
            File::put($appCss, $updated);
            $this->line("  ✓ Тему встановлено: {$theme}");
        }

        $this->newLine();
        $this->info('Готово! Наступні кроки:');
        $this->line('   1. Перебудуйте CSS:    npm run build');
        $this->line('   2. Запустіть міграції: php artisan migrate');
        $this->line('   3. Sync довідників:    php artisan np:sync && php artisan up:sync-references');
        $this->line('   4. Налаштуйте API ключі в /admin/shipping-providers (NP) і /admin/ukr-poshta-settings (UP)');
        $this->line('   5. Створіть товари або імпортуйте їх через ProductSeeder');
        $this->newLine();

        return self::SUCCESS;
    }
}
