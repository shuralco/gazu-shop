<?php

namespace Tests\Feature\Gazu;

use App\Models\Product;
use App\Support\UploadedImage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Галерея товару: додаткові зображення мусять зберігатись і показуватись.
 *
 * Регресія 25.08.2026 — дві помилки одна на одній:
 *   1) у формі поле звалось `gallery_images`, атрибута з такою назвою в Product
 *      немає → Filament зберігав його в нікуди: файли лягали на диск, у БД
 *      не потрапляло нічого (на проді знайшлось 5 осиротілих файлів);
 *   2) акcесор клеїв провідний слеш, тож UploadedImage::url() шукав аплоад у
 *      public/products/... замість public/storage/products/... → навіть
 *      збережена галерея не рендерилась.
 */
class ProductGalleryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_gallery_keeps_upload_paths_untouched(): void
    {
        $product = Product::factory()->create([
            'gallery' => ['products/gallery/aaa.jpg', 'products/gallery/bbb.jpg'],
        ]);

        // Саме disk-relative, БЕЗ провідного слеша — цього чекає UploadedImage::url().
        $this->assertSame(
            ['products/gallery/aaa.jpg', 'products/gallery/bbb.jpg'],
            $product->fresh()->gallery
        );
    }

    public function test_gallery_upload_path_is_looked_up_under_storage(): void
    {
        // public/storage — симлінк на storage/app/public. Локально він указує на
        // шлях контейнера й не резолвиться, тож перевіряти файлом можна лише там,
        // де симлінк живий (прод/CI-образ).
        if (! is_dir(public_path('storage'))) {
            $this->markTestSkipped('public/storage не змонтовано в цьому середовищі');
        }

        $dir = public_path('storage/products/gallery');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/probe-gallery.jpg', 'x');

        try {
            UploadedImage::flush();
            $this->assertStringEndsWith(
                '/storage/products/gallery/probe-gallery.jpg',
                (string) UploadedImage::url('products/gallery/probe-gallery.jpg')
            );
        } finally {
            File::delete($dir.'/probe-gallery.jpg');
            UploadedImage::flush();
        }
    }

    /** Шлях аплоаду НЕ має бути public-відносним — інакше файл шукається не там. */
    public function test_gallery_path_is_not_public_relative(): void
    {
        $product = Product::factory()->create(['gallery' => ['products/gallery/aaa.jpg']]);

        UploadedImage::flush();
        // Провідного слеша немає → UploadedImage допише storage/ (див. тест вище).
        $this->assertStringStartsNotWith('/', $product->fresh()->gallery[0]);
    }

    public function test_legacy_demo_names_still_map_to_assets(): void
    {
        $product = Product::factory()->create(['gallery' => ['4.jpg']]);

        $this->assertSame(['/assets/img/products/4.jpg'], $product->fresh()->gallery);
    }

    public function test_form_field_targets_real_model_attribute(): void
    {
        // Поле форми мусить називатись як атрибут моделі, інакше дані губляться.
        $form = File::get(app_path('Filament/Resources/ProductResource.php'));

        $this->assertStringContainsString("FileUpload::make('gallery')", $form);
        $this->assertStringNotContainsString("FileUpload::make('gallery_images')", $form);
        $this->assertContains('gallery', (new Product)->getFillable());
    }
}
