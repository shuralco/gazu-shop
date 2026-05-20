<?php

namespace Tests\Feature\Gazu;

use App\Models\CarMake;
use App\Models\CarModel;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Smoke + behaviour tests for the GAZU storefront (gazu.uno) and the recent
 * admin-editability / taxonomy / cart-drawer work.
 */
class GazuStorefrontTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @dataProvider publicPages */
    public function test_public_pages_load(string $url): void
    {
        $this->get($url)->assertStatus(200);
    }

    public static function publicPages(): array
    {
        return [
            'home'      => ['/'],
            'catalog'   => ['/catalog'],
            'brands'    => ['/brand'],
            'blog'      => ['/blog'],
            'contacts'  => ['/contacts'],
            'login'     => ['/login'],
            'cart'      => ['/cart'],
            'search'    => ['/search'],
            'delivery'  => ['/delivery'],
            'warranty'  => ['/warranty'],
            'novynky'   => ['/novynky'],
            'khity'     => ['/khity'],
            'akcii'     => ['/akcii'],
        ];
    }

    public function test_404_page_renders_with_status_404(): void
    {
        $this->get('/neisnuyucha-storinka-xyz-'.uniqid())
            ->assertStatus(404)
            ->assertSee('404', false);
    }

    public function test_delivery_page_has_consistent_free_shipping_threshold(): void
    {
        $html = $this->get('/delivery')->assertStatus(200)->getContent();
        // Поріг безкоштовної доставки уніфіковано на 1500 — не має бути 1000.
        $this->assertStringNotContainsString('1 000', $html);
    }

    public function test_db_backed_info_page_renders(): void
    {
        // Сторінки на кшталт /faq не мають code-fallback — рендеряться з DB.
        \App\Models\InfoPage::create([
            'slug' => 'faq',
            'title' => 'Часті питання',
            'intro' => 'Відповіді на типові питання.',
            'is_active' => true,
        ]);

        $this->get('/faq')->assertStatus(200)->assertSee('Часті питання', false);
    }

    public function test_zapchastyny_page_uses_make_name_in_title(): void
    {
        $make = CarMake::create([
            'slug' => 'chery', 'name' => 'Chery', 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->get('/zapchastyny/'.$make->slug)
            ->assertStatus(200)
            ->assertSee('Chery', false);
    }
}
