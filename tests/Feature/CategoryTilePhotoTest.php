<?php

namespace Tests\Feature;

use App\Support\PartImage;
use Tests\TestCase;

/**
 * Тематичні фото для плиток категорій.
 *
 * Пастка, на яку наступили: правила з кількох слів («гальмівн рідин») як
 * підрядок НЕ спрацьовували — між словами стоїть українське закінчення
 * («гальмівнА рідинА»), тож «Гальмівна рідина» отримувала фото гальмівного
 * диска замість каністри.
 */
class CategoryTilePhotoTest extends TestCase
{
    public static function categories(): array
    {
        return [
            // назва категорії          => очікуване фото
            ['Автомобільні оливи',        'oil'],
            ['Трансмісійні оливи',        'oil'],
            ['Технічні рідини',           'oil'],
            ['Гальмівна рідина',          'oil'],
            ['Антифриз',                  'coolant'],
            ['Фільтри',                   'filter'],
            ['Фільтри повітря в салоні',  'filter'],
            ['Комплект ТО',               'filter'],
            ['Акумулятори',               'battery'],
            ['Щітки склоочисника',        'wiper'],
            ['Деталі салону',             'mat'],
            ['Запчастини електрики та освітлення', 'bulb'],
            ['Деталі для ТО',              'filter'],
        ];
    }

    /** @dataProvider categories */
    public function test_category_gets_matching_photo(string $title, string $expected): void
    {
        $this->assertSame($expected, PartImage::kindFromCategory($title), "категорія «{$title}»");
    }

    public function test_multiword_rule_survives_ukrainian_endings(): void
    {
        // саме через це «Гальмівна рідина» падала на загальне 'гальм' → диск
        $this->assertSame('oil', PartImage::kindFromCategory('Гальмівна рідина DOT 4 Оригінал'));
        $this->assertNotSame('brake-disc', PartImage::kindFromCategory('Гальмівна рідина'));
    }

    public function test_specific_rule_wins_over_general(): void
    {
        // «фільтр» важливіший за «салон», інакше фільтр салона отримав би килимок
        $this->assertSame('filter', PartImage::kindFromCategory('Фільтри повітря в салоні'));
        // «щітк» важливіше за «скло», інакше щітки отримали б дзеркало
        $this->assertSame('wiper', PartImage::kindFromCategory('Щітки склоочисника'));
        // «антифриз» важливіший за загальне «рідин»
        $this->assertSame('coolant', PartImage::kindFromCategory('Антифриз VAG G12'));
    }

    public function test_service_parts_rule_does_not_overmatch(): void
    {
        // «детал то» вимагає обидва слова: інакше правило хапало б будь-що з «то»
        $this->assertSame('filter', PartImage::kindFromCategory('Деталі для ТО'));
        $this->assertSame('mat', PartImage::kindFromCategory('Деталі салону'));
        $this->assertSame('oil', PartImage::kindFromCategory('Автомобільні оливи'));
    }

    public function test_unknown_category_gets_no_photo(): void
    {
        // Для ущільнень і пробок відповідного набору немає — краще іконка,
        // ніж фото не з тієї теми.
        $this->assertNull(PartImage::kindFromCategory('Ущільнення та пробки'));
        $this->assertNull(PartImage::kindFromCategory('Пробка зливна/заливна'));
    }

    public function test_resolved_photo_file_exists(): void
    {
        foreach (self::categories() as [$title, $kind]) {
            $url = PartImage::resolve(null, $kind, $title, $title);

            $this->assertStringStartsNotWith('data:', $url, "«{$title}» мусить дати справжнє фото, а не монограму");
            $path = public_path(parse_url($url, PHP_URL_PATH));
            $this->assertFileExists($path, "файл для «{$title}»");
        }
    }
}
