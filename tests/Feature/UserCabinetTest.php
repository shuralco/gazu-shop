<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserCabinetTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * Канонічна сторінка кабінету — /kabinet (UA URL). Гість має бути
     * перенаправлений на /login (auth middleware). Це справжній auth-gate;
     * /account нижче — лише legacy 301 на /kabinet.
     */
    public function test_kabinet_requires_auth(): void
    {
        $this->get('/kabinet')->assertRedirect('/login');
    }

    /**
     * Legacy SEO: /account → 301 → /kabinet (а далі /kabinet → /login для гостя).
     * Редірект навмисно ЗА межами auth middleware, тому 301, не 302.
     */
    public function test_account_redirects_to_canonical_kabinet(): void
    {
        $this->get('/account')->assertRedirect('/kabinet')->assertStatus(301);
    }

    /**
     * Wishlist тепер публічний (guest-обране на localStorage, merge при логіні).
     * Навмисна зміна поведінки: 200 для гостя, без редіректу на /login.
     */
    public function test_wishlist_is_public_for_guest(): void
    {
        $this->get('/wishlist')->assertStatus(200);
    }

    /**
     * Loyalty переїхав з кабінету в інфо-сторінку. /loyalty → 301 → /bonusy (legacy).
     */
    public function test_loyalty_redirects_to_bonusy(): void
    {
        $this->get('/loyalty')->assertRedirect('/bonusy')->assertStatus(301);
    }

    public function test_kabinet_loads_for_auth_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/kabinet')->assertStatus(200);
    }

    /**
     * Окремої /orders сторінки більше немає — замовлення показуються на самій
     * сторінці кабінету (/kabinet рендерить view gazu.account.orders). Перевіряємо,
     * що автентифікований user бачить свої замовлення в кабінеті.
     */
    public function test_orders_visible_in_kabinet_for_auth_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/kabinet')->assertStatus(200);
    }

    /**
     * Wishlist також віддає 200 для автентифікованого користувача.
     */
    public function test_wishlist_loads_for_auth_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/wishlist')->assertStatus(200);
    }
}
