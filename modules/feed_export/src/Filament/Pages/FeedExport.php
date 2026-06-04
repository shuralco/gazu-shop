<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\RequiresModule;
use App\Models\Product;
use App\Services\FeedGenerator\YmlFeedGenerator;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

/**
 * Адмін-панель експорту YML/XML-фідів для маркетплейсів.
 */
class FeedExport extends Page
{
    use RequiresModule;

    protected static string $moduleKey = 'feed_export';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';

    protected static ?string $navigationLabel = 'Експорт фідів';

    protected static ?string $navigationGroup = 'Каталог';

    protected static ?string $title = 'Експорт фідів на маркетплейси';

    protected static ?int $navigationSort = 120;

    protected static ?string $slug = 'feed-export';

    protected static string $view = 'filament.pages.feed-export';

    public array $feeds = [];

    public function mount(): void
    {
        $this->feeds = $this->loadFeeds();
    }

    private function loadFeeds(): array
    {
        $appUrl = rtrim(config('app.url'), '/');

        $defs = [
            ['type' => 'rozetka', 'name' => 'Rozetka', 'icon' => '🛒', 'route' => 'feed.rozetka',
                'description' => 'YML-фід для маркетплейсу Rozetka. Підходить для більшості укр. маркетплейсів.'],
            ['type' => 'prom',    'name' => 'Prom.ua', 'icon' => '🏪', 'route' => 'feed.prom',
                'description' => 'YML-фід для Prom.ua. Можна вантажити через ЛК продавця.'],
            ['type' => 'olx',     'name' => 'OLX',     'icon' => '📦', 'route' => 'feed.olx',
                'description' => 'XML adverts.xml для OLX.ua. Імпорт через "Магазин" → "Імпорт оголошень".'],
            ['type' => 'google',  'name' => 'Google Shopping', 'icon' => '🌐', 'route' => 'feed.google',
                'description' => 'YML-фід з g:condition / g:availability полями для Merchant Center.'],
        ];

        $list = [];
        foreach ($defs as $f) {
            $f['url'] = $appUrl.'/feed/'.$f['type'].'.xml';
            $f['last_at'] = Cache::get("product_feed_{$f['type']}_at");
            $f['cached']  = Cache::has("product_feed_{$f['type']}");
            $list[] = $f;
        }
        return $list;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regenerateAll')
                ->label('Перегенерувати всі')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Перегенерувати усі фіди?')
                ->modalDescription('Кеш буде очищено. Наступний запит до /feed/*.xml побудує свіжий XML (може зайняти 5-30 секунд за умови великого каталогу).')
                ->action(function () {
                    app(YmlFeedGenerator::class)->clearCache();
                    Notification::make()->title('Кеш фідів очищено')->success()->send();
                    $this->feeds = $this->loadFeeds();
                }),
        ];
    }

    public function regenerate(string $type): void
    {
        if (! in_array($type, ['rozetka', 'prom', 'olx', 'google'], true)) {
            Notification::make()->title('Невідомий тип фіду')->danger()->send();
            return;
        }

        try {
            $start = microtime(true);
            // Проганяємо генерацію зразу, щоб кеш заповнився
            app(YmlFeedGenerator::class)->clearCache($type);
            $xml = app(YmlFeedGenerator::class)->generate($type);
            $duration = round((microtime(true) - $start) * 1000);
            $size = strlen($xml);

            Notification::make()
                ->title("Фід {$type} згенеровано")
                ->body("Розмір: ".number_format($size / 1024, 1, '.', ' ')." КБ · {$duration} мс")
                ->success()
                ->send();

            $this->feeds = $this->loadFeeds();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Помилка генерації')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearOne(string $type): void
    {
        app(YmlFeedGenerator::class)->clearCache($type);
        Notification::make()->title("Кеш {$type} очищено")->success()->send();
        $this->feeds = $this->loadFeeds();
    }

    public function getViewData(): array
    {
        $totalProducts = Product::where('is_active', true)->count();
        $withImage = Product::where('is_active', true)->whereNotNull('image')->where('image', '!=', '')->count();
        $inStock = Product::where('is_active', true)->where('stock_status', 'in_stock')->count();

        return [
            'totalProducts' => $totalProducts,
            'withImage' => $withImage,
            'inStock' => $inStock,
        ];
    }
}
