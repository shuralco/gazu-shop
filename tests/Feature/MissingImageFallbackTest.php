<?php

namespace Tests\Feature;

use App\Models\CarMake;
use App\Models\Product;
use App\Support\UploadedImage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Зникле завантажене зображення не має давати битий <img>.
 *
 * Регресія: аплоади лежать у storage/app/public (тека контейнера). Без
 * постійного тому перезбірка стирає файли, а в БД лишаються шляхи — покупець
 * бачив порожні квадрати замість фото товарів і логотипів марок.
 */
class MissingImageFallbackTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        UploadedImage::flush();
    }

    public function test_missing_upload_resolves_to_null(): void
    {
        $this->assertNull(UploadedImage::url('products/does-not-exist.jpg'));
        $this->assertNull(UploadedImage::url('/img/car-makes/no-such-brand.svg'));
    }

    public function test_blank_input_resolves_to_null(): void
    {
        $this->assertNull(UploadedImage::url(null));
        $this->assertNull(UploadedImage::url(''));
        $this->assertNull(UploadedImage::url('   '));
    }

    public function test_external_url_is_returned_as_is(): void
    {
        // Чужий домен не наш диск — не перевіряємо існування.
        $this->assertSame('https://cdn.example.com/a.png', UploadedImage::url('https://cdn.example.com/a.png'));
        $this->assertStringStartsWith('data:', (string) UploadedImage::url('data:image/svg+xml;base64,AAA'));
    }

    public function test_existing_public_file_resolves_to_url(): void
    {
        // Файл із репозиторію — гарантовано на диску.
        $url = UploadedImage::url('/img/car-makes/vw.svg');

        $this->assertNotNull($url);
        $this->assertStringEndsWith('/img/car-makes/vw.svg', $url);
    }

    public function test_car_make_falls_back_to_repo_logo_when_upload_is_gone(): void
    {
        $make = CarMake::create([
            'slug' => 'vw', 'name' => 'Volkswagen', 'is_active' => true,
            'logo_path' => 'car-makes/01KXAZFHXYT955G8F99H4CCETC.png', // зниклий аплоад
        ]);

        $this->assertStringEndsWith('/img/car-makes/vw.svg', (string) $make->logo_url,
            'замість битого аплоада — лого з репозиторію за slug');
    }

    public function test_car_make_without_repo_logo_returns_null(): void
    {
        $make = CarMake::create([
            'slug' => 'no-such-brand', 'name' => 'Невідома', 'is_active' => true,
            'logo_path' => 'car-makes/gone.png',
        ]);

        $this->assertNull($make->logo_url, 'нема ні аплоада, ні репо-асета → літерний бейдж');
    }

    public function test_restore_command_repoints_make_and_clears_product(): void
    {
        $make = CarMake::create([
            'slug' => 'vw', 'name' => 'Volkswagen', 'is_active' => true,
            'logo_path' => 'car-makes/gone.png',
        ]);
        $product = Product::factory()->create(['is_active' => true, 'image' => 'products/gone.jpg']);

        $this->artisan('gazu:restore-images')->assertSuccessful();

        $this->assertSame('/img/car-makes/vw.svg', $make->fresh()->logo_path, 'марка → лого з репо');
        $this->assertNull($product->fresh()->image, 'товар → плейсхолдер');
    }

    public function test_restore_command_leaves_valid_paths_alone(): void
    {
        $make = CarMake::create([
            'slug' => 'byd', 'name' => 'BYD', 'is_active' => true,
            'logo_path' => '/img/car-makes/byd.png',
        ]);

        $this->artisan('gazu:restore-images')->assertSuccessful();

        $this->assertSame('/img/car-makes/byd.png', $make->fresh()->logo_path);
    }

    public function test_dry_run_changes_nothing(): void
    {
        $make = CarMake::create([
            'slug' => 'vw', 'name' => 'Volkswagen', 'is_active' => true,
            'logo_path' => 'car-makes/gone.png',
        ]);

        $this->artisan('gazu:restore-images', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame('car-makes/gone.png', $make->fresh()->logo_path);
    }

    public function test_new_brand_logos_are_present_in_repo(): void
    {
        // Ці лого відновлені після втрати аплоадів — вони мусять лежати в репо,
        // інакше після наступної перезбірки марки знову лишаться без логотипів.
        foreach (['zeekr', 'tesla', 'skoda', 'cupra', 'vw', 'byd', 'audi'] as $slug) {
            $this->assertNotNull(
                CarMake::repoLogoUrl($slug),
                "лого «{$slug}» має бути в public/img/car-makes/"
            );
        }
    }

    public function test_restore_command_repoints_brand_logo_and_clears_category(): void
    {
        $brand = \App\Models\Brand::create(['name' => 'BYD', 'slug' => 'byd', 'logo' => 'brands/logos/gone.png', 'is_active' => true]);
        $noRepo = \App\Models\Brand::create(['name' => 'VW-FAW', 'slug' => 'vw-faw', 'logo' => 'brands/logos/gone2.png', 'is_active' => true]);
        $cat = \App\Models\Category::create(['title' => 'Фільтри', 'slug' => 'filtry', 'is_active' => true, 'image' => 'categories/gone.png']);

        $this->artisan('gazu:restore-images')->assertSuccessful();

        $this->assertSame('/img/car-makes/byd.svg', $brand->fresh()->logo, 'бренд із репо-асетом → лого з репо');
        $this->assertNull($noRepo->fresh()->logo, 'без репо-асета → очищено, буде назва замість битого лого');
        $this->assertNull($cat->fresh()->image, 'мертве зображення категорії очищено');
    }

    public function test_restore_command_drops_only_missing_gallery_frames(): void
    {
        $p = Product::factory()->create([
            'is_active' => true,
            'image' => '/img/car-makes/vw.svg',
            'gallery' => ['/img/car-makes/byd.png', 'products/main/gone.jpg', '/img/car-makes/audi.svg'],
        ]);

        $this->artisan('gazu:restore-images')->assertSuccessful();

        $this->assertSame(
            ['/img/car-makes/byd.png', '/img/car-makes/audi.svg'],
            $p->fresh()->gallery,
            'зниклий кадр прибрано, живі лишились'
        );
        $this->assertSame('/img/car-makes/vw.svg', $p->fresh()->image, 'головне фото не чіпаємо');
    }
}
