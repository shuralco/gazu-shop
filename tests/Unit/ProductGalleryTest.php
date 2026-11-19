<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Support\ProductGallery;
use App\Support\UploadedImage;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Поле форми gallery_images ↔ колонка products.gallery. Раніше ніщо їх не
 * з'єднувало: фото завантажувались, але до товару не прив'язувались.
 */
class ProductGalleryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_form_paths_become_storage_urls_and_back(): void
    {
        $stored = ProductGallery::forStorage(['products/gallery/a.jpg', 'products/gallery/b.png'], null);

        $this->assertSame(['/storage/products/gallery/a.jpg', '/storage/products/gallery/b.png'], $stored);
        $this->assertSame(['products/gallery/a.jpg', 'products/gallery/b.png'], ProductGallery::forForm($stored));
    }

    public function test_empty_form_clears_gallery(): void
    {
        $this->assertNull(ProductGallery::forStorage([], null));
    }

    public function test_legacy_entries_survive_gallery_edit(): void
    {
        $stored = ProductGallery::forStorage(['products/gallery/a.jpg'], ['/assets/img/products/4.jpg', '/storage/products/gallery/old.jpg']);

        $this->assertSame(['/storage/products/gallery/a.jpg', '/assets/img/products/4.jpg'], $stored);
    }

    public function test_saved_gallery_roundtrips_through_the_model(): void
    {
        $p = Product::factory()->create(['is_active' => true]);
        $p->update(['gallery' => ProductGallery::forStorage(['products/gallery/x.jpg', 'products/gallery/y.jpg'], null)]);

        $fresh = $p->fresh();
        $this->assertSame(['/storage/products/gallery/x.jpg', '/storage/products/gallery/y.jpg'], $fresh->gallery);
        $this->assertSame(['products/gallery/x.jpg', 'products/gallery/y.jpg'], ProductGallery::forForm($fresh->gallery));
    }

    public function test_storefront_resolver_accepts_the_stored_format(): void
    {
        // UploadedImage::url() вважає шлях із «/» відносним до public/ — формат
        // /storage/... саме тому. Перевіряємо на тимчасовому файлі в public/.
        $dir = public_path('phpunit-gallery-tmp');
        mkdir($dir, 0777, true);
        file_put_contents($dir.'/a.jpg', 'x');

        try {
            $this->assertNotNull(UploadedImage::url('/phpunit-gallery-tmp/a.jpg'));
            $this->assertNull(UploadedImage::url('/phpunit-gallery-tmp/missing.jpg'));
        } finally {
            @unlink($dir.'/a.jpg');
            @rmdir($dir);
        }
    }
}
