<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Галерея товару = реальні зображення, без клонів.
 *
 * Регресія: коли у товару було справжнє фото, галерея все одно робила 4
 * слоти-«ракурси» й у кожному показувала ОДИН файл — 4 однакові слайди, 4
 * однакові мініатюри, лічильник «1 / 4».
 */
class ProductGalleryTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function html(Product $p): string
    {
        return $this->get('/'.$p->getLocalizedSlug())->assertOk()->getContent();
    }

    /**
     * Скільки слайдів у галереї — читаємо з лічильника «<span…>1</span> / N».
     * Він рендериться в кількох блоках (десктоп, zoom), значення мусять збігатись.
     */
    private function slideCount(string $html): int
    {
        preg_match_all('#x-text="idx \+ 1">1</span>\s*/\s*(\d+)#', $html, $m);
        $this->assertNotEmpty($m[1], 'лічильник слайдів не знайдено в розмітці');
        $this->assertCount(1, array_unique($m[1]), 'лічильники в різних блоках розходяться');

        return (int) $m[1][0];
    }

    private function product(array $attrs = []): Product
    {
        return Product::factory()->create($attrs + ['is_active' => true]);
    }

    public function test_single_photo_is_not_cloned_into_four_slides(): void
    {
        // Файл із репозиторію — гарантовано існує, тож проходить перевірку.
        $p = $this->product(['image' => '/img/car-makes/vw.svg', 'gallery' => []]);

        $html = $this->html($p);

        $this->assertSame(1, $this->slideCount($html), 'одне фото = один слайд, а не чотири');
        $this->assertStringContainsString('/img/car-makes/vw.svg', $html, 'фото мусить бути на сторінці');
    }

    public function test_counter_shows_one_slide_for_single_photo(): void
    {
        $p = $this->product(['image' => '/img/car-makes/vw.svg', 'gallery' => []]);

        $this->assertSame(1, $this->slideCount($this->html($p)), 'лічильник має показувати 1 слайд');
    }

    public function test_extra_gallery_images_become_separate_slides(): void
    {
        $p = $this->product([
            'image' => '/img/car-makes/vw.svg',
            'gallery' => ['/img/car-makes/byd.png', '/img/car-makes/audi.svg'],
        ]);

        $html = $this->html($p);

        $this->assertSame(3, $this->slideCount($html), 'головне + 2 додаткові = 3 слайди');
        $this->assertStringContainsString('/img/car-makes/byd.png', $html);
        $this->assertStringContainsString('/img/car-makes/audi.svg', $html);
    }

    public function test_gallery_skips_missing_and_duplicate_files(): void
    {
        $p = $this->product([
            'image' => '/img/car-makes/vw.svg',
            'gallery' => [
                '/img/car-makes/vw.svg',          // дубль головного
                'products/main/gone-forever.jpg', // зниклий файл
                '/img/car-makes/byd.png',         // валідний
            ],
        ]);

        $html = $this->html($p);

        $this->assertSame(2, $this->slideCount($html), 'лишаються головне + один валідний');
        $this->assertStringNotContainsString('gone-forever.jpg', $html, 'зниклий файл не рендеримо');
    }

    public function test_product_without_photo_gets_single_placeholder(): void
    {
        $p = $this->product(['image' => null, 'gallery' => []]);

        $html = $this->html($p);

        $this->assertSame(1, $this->slideCount($html), 'без фото — один генеративний плейсхолдер');
        $this->assertStringContainsString('data:image/svg+xml;base64,', $html);
    }
}
