<?php

namespace Tests\Feature;

use App\Models\AccessPreset;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * gazu:access — призначення RBAC-пресету й скидання персонального приховування меню.
 */
class AccessGrantTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\AccessPresetSeeder::class);
    }

    public function test_assigns_client_admin_preset_by_email(): void
    {
        $u = User::factory()->create(['is_admin' => false, 'access_preset_id' => null]);
        $preset = AccessPreset::where('key', 'client_admin')->firstOrFail();

        $this->artisan('gazu:access', ['email' => $u->email])->assertSuccessful();

        $this->assertSame($preset->id, $u->fresh()->access_preset_id);
    }

    public function test_clear_hidden_resets_personal_menu_hiding(): void
    {
        $u = User::factory()->create([
            'is_admin' => false,
            'nav_preferences' => ['hidden' => ['FilterResource', 'ProductResource']],
        ]);

        $this->artisan('gazu:access', ['email' => $u->email, '--clear-hidden' => true])->assertSuccessful();

        $this->assertSame([], $u->fresh()->nav_preferences['hidden']);
    }

    public function test_hidden_is_left_untouched_without_flag(): void
    {
        $u = User::factory()->create([
            'is_admin' => false,
            'nav_preferences' => ['hidden' => ['FilterResource']],
        ]);

        $this->artisan('gazu:access', ['email' => $u->email])->assertSuccessful();

        $this->assertSame(['FilterResource'], $u->fresh()->nav_preferences['hidden']);
    }

    public function test_unknown_email_fails(): void
    {
        $this->artisan('gazu:access', ['email' => 'nobody@example.com'])->assertFailed();
    }

    public function test_unknown_preset_fails(): void
    {
        $u = User::factory()->create(['is_admin' => false]);

        $this->artisan('gazu:access', ['email' => $u->email, '--preset' => 'does_not_exist'])->assertFailed();
    }

    public function test_list_runs_without_error(): void
    {
        User::factory()->create(['is_admin' => false]);

        $this->artisan('gazu:access', ['--list' => true])->assertSuccessful();
    }
}
