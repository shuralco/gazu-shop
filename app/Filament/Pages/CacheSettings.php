<?php

namespace App\Filament\Pages;

use App\Models\DisplaySetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Налаштування cache-стеку: TTL per domain, on/off toggles, excluded URLs,
 * warmup URLs, Octane info, live статистика hit-rate з Redis.
 *
 * Зберігається у DisplaySetting (Redis-cache всередині). При зміні —
 * автоматично flush'имо response cache щоб налаштування вступили в дію.
 */
class CacheSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationLabel = 'Cache налаштування';
    protected static ?string $title = 'Швидкість & Cache налаштування';
    protected static ?string $navigationGroup = 'Система';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.cache-settings';

    public ?array $data = [];

    /** Default TTLs (seconds). Перевизначаються через DisplaySetting. */
    public const DEFAULTS = [
        'cache_response_enabled' => true,
        'cache_response_ttl' => 604800,        // 7 days for response cache
        'cache_products_ttl' => 3600,          // 1 hour for product queries
        'cache_categories_ttl' => 21600,       // 6 hours
        'cache_brands_ttl' => 21600,           // 6 hours
        'cache_blog_ttl' => 86400,             // 1 day
        'cache_info_ttl' => 86400,             // 1 day
        'cache_cars_ttl' => 86400,             // 1 day (rarely changes)
        'cache_settings_ttl' => 21600,         // 6 hours
        'cache_excluded_paths' => "admin\ncart\ncheckout\naccount\nlogin\nregister\napi\nstorage\nlivewire",
        'cache_warmup_urls' => "/\n/catalog\n/novynky\n/khity\n/akcii\n/blog\n/about",
        'cache_warmup_enabled' => false,        // auto-warmup після flush
    ];

    public function mount(): void
    {
        $data = [];
        foreach (self::DEFAULTS as $key => $default) {
            $value = DisplaySetting::get($key);
            $data[$key] = $value !== null ? $value : $default;
        }
        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->tabs([

                // ── 1. Response cache (HTML) ─────────────────────────────
                Forms\Components\Tabs\Tab::make('Response cache (HTML)')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Section::make('Spatie ResponseCache')
                            ->description('Кеш повного HTML на рівні запиту. Найбільший speedup. Storage: Redis.')
                            ->schema([
                                Forms\Components\Toggle::make('cache_response_enabled')
                                    ->label('Увімкнути response cache')
                                    ->helperText('Вимкніть під час розробки коли потрібно бачити зміни одразу. На проді — завжди ON.')
                                    ->default(true),
                                Forms\Components\Select::make('cache_response_ttl')
                                    ->label('Тривалість зберігання (TTL)')
                                    ->options($this->ttlOptions())
                                    ->required()
                                    ->helperText('Як довго HTML лежить у Redis до першого rebuild. Чим довше — тим менше навантаження на БД.'),
                            ]),
                    ]),

                // ── 2. Application cache TTL per domain ──────────────────
                Forms\Components\Tabs\Tab::make('TTL по доменах')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\Section::make('Cache::tags() TTL для кожного домену')
                            ->description('Скільки часу окремі DB-запити кешуються в Redis. Auto-flush через Eloquent observers.')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Select::make('cache_products_ttl')
                                        ->label('Товари (products)')
                                        ->options($this->ttlOptions())->required(),
                                    Forms\Components\Select::make('cache_categories_ttl')
                                        ->label('Категорії (categories)')
                                        ->options($this->ttlOptions())->required(),
                                    Forms\Components\Select::make('cache_brands_ttl')
                                        ->label('Бренди (brands)')
                                        ->options($this->ttlOptions())->required(),
                                    Forms\Components\Select::make('cache_blog_ttl')
                                        ->label('Блог + статті (blog)')
                                        ->options($this->ttlOptions())->required(),
                                    Forms\Components\Select::make('cache_info_ttl')
                                        ->label('Info сторінки (info)')
                                        ->options($this->ttlOptions())->required(),
                                    Forms\Components\Select::make('cache_cars_ttl')
                                        ->label('Авто-сумісність (cars)')
                                        ->options($this->ttlOptions())->required(),
                                    Forms\Components\Select::make('cache_settings_ttl')
                                        ->label('Налаштування магазину (settings)')
                                        ->options($this->ttlOptions())->required(),
                                ]),
                            ]),
                    ]),

                // ── 3. Excluded / Warmup URLs ────────────────────────────
                Forms\Components\Tabs\Tab::make('Виключення + Warmup')
                    ->icon('heroicon-o-funnel')
                    ->schema([
                        Forms\Components\Section::make('Виключені URL prefixes')
                            ->description('Префікси шляхів, які НЕ кешуються. По одному на рядок.')
                            ->schema([
                                Forms\Components\Textarea::make('cache_excluded_paths')
                                    ->label('Path prefixes')
                                    ->rows(8)
                                    ->helperText('Кожен рядок = окремий prefix. Auth-only сторінки (cart, checkout, account) повинні бути тут.'),
                            ]),
                        Forms\Components\Section::make('Auto-warmup')
                            ->description('Сторінки, які rebuild\'ються одразу після cache:clear (HTTP GET через cron / artisan command).')
                            ->schema([
                                Forms\Components\Toggle::make('cache_warmup_enabled')
                                    ->label('Auto-warmup після flush')
                                    ->helperText('Якщо ON — після cache:clear автоматично GET всі URLs нижче.'),
                                Forms\Components\Textarea::make('cache_warmup_urls')
                                    ->label('Warmup URLs')
                                    ->rows(8)
                                    ->helperText('По одному URL на рядок. Без https://.'),
                            ]),
                    ]),

                // ── 4. Octane (read-only info) ───────────────────────────
                Forms\Components\Tabs\Tab::make('Octane / OPcache')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Forms\Components\Section::make('Octane (Swoole) — runtime info')
                            ->description('Налаштовується у docker-compose / Dockerfile, не змінюється з UI. Для зміни — оновити Dockerfile + redeploy.')
                            ->schema([
                                Forms\Components\Placeholder::make('octane_info')
                                    ->content(function () {
                                        $rows = [
                                            'Swoole extension' => extension_loaded('swoole') ? 'INSTALLED' : 'NOT INSTALLED',
                                            'OpenSwoole extension' => extension_loaded('openswoole') ? 'INSTALLED' : 'NOT INSTALLED',
                                            'OPcache enabled' => function_exists('opcache_get_status') && opcache_get_status(false) !== false ? 'YES' : 'NO',
                                            'PHP memory_limit' => ini_get('memory_limit'),
                                            'PHP version' => phpversion(),
                                            'Laravel version' => app()->version(),
                                        ];
                                        $html = '<table class="text-sm w-full"><tbody>';
                                        foreach ($rows as $k => $v) {
                                            $html .= '<tr><td class="py-1 pr-3 text-gray-500">'.e($k).'</td><td class="py-1 font-mono font-semibold">'.e($v).'</td></tr>';
                                        }
                                        $html .= '</tbody></table>';
                                        return new \Illuminate\Support\HtmlString($html);
                                    }),
                            ]),
                    ]),
            ])->columnSpanFull(),
        ]);
    }

    public function save(): void
    {
        $values = $this->form->getState();
        foreach ($values as $key => $value) {
            if (is_bool($value)) $value = $value ? '1' : '0';
            DisplaySetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'is_active' => true]
            );
        }
        // Drop cached DisplaySettings + responseCache to apply changes immediately.
        Cache::forget('display_settings_all');
        ResponseCache::clear();

        Notification::make()
            ->title('Налаштування збережено')
            ->body('Усі response cache було очищено для застосування нових TTL.')
            ->success()
            ->send();
    }

    public function getHitStats(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info('stats');
            return [
                'keyspace_hits' => $info['Stats']['keyspace_hits'] ?? $info['keyspace_hits'] ?? 0,
                'keyspace_misses' => $info['Stats']['keyspace_misses'] ?? $info['keyspace_misses'] ?? 0,
                'total_commands_processed' => $info['Stats']['total_commands_processed'] ?? $info['total_commands_processed'] ?? 0,
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function ttlOptions(): array
    {
        return [
            60 => '1 хв',
            300 => '5 хв',
            900 => '15 хв',
            1800 => '30 хв',
            3600 => '1 година',
            10800 => '3 години',
            21600 => '6 годин',
            43200 => '12 годин',
            86400 => '1 день',
            259200 => '3 дні',
            604800 => '7 днів',
            2592000 => '30 днів',
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Зберегти')
                ->submit('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
            Forms\Components\Actions\Action::make('reset')
                ->label('Скинути до дефолтів')
                ->color('gray')
                ->icon('heroicon-o-arrow-uturn-left')
                ->requiresConfirmation()
                ->action(function () {
                    foreach (self::DEFAULTS as $key => $default) {
                        DisplaySetting::where('key', $key)->delete();
                    }
                    Cache::forget('display_settings_all');
                    $this->mount();
                    Notification::make()->title('Скинуто до дефолтів')->success()->send();
                }),
        ];
    }
}
