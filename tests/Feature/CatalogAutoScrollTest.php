<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Автоскрол до результатів: після фільтрів або зміни сторінки користувач
 * має бачити товари, а не лишатись унизу біля пагінації.
 */
class CatalogAutoScrollTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_results_have_anchor(): void
    {
        $this->get('/catalog')->assertOk()->assertSee('id="gazu-results"', false);
    }

    public function test_script_handles_both_navigation_types(): void
    {
        $html = $this->get('/catalog')->assertOk()->getContent();

        // фільтри — звичайний GET (перезавантаження)
        $this->assertStringContainsString('DOMContentLoaded', $html);
        // пагінація — wire:navigate (без перезавантаження)
        $this->assertStringContainsString('livewire:navigated', $html);
    }

    public function test_clean_catalog_does_not_scroll(): void
    {
        $html = $this->get('/catalog')->assertOk()->getContent();

        // Скрол лише коли в адресі є параметри вибірки
        $this->assertStringContainsString("KEYS.some", $html);
        $this->assertStringContainsString("'page'", $html);
    }

    public function test_retries_and_yields_to_user(): void
    {
        $html = $this->get('/catalog')->assertOk()->getContent();

        // одного скролу мало: зображення довантажуються й зсувають розмітку
        $this->assertStringContainsString('[0, 250, 700]', $html);
        // будь-яка дія користувача скасовує решту спроб
        $this->assertStringContainsString("'wheel'", $html);
        $this->assertStringContainsString('cancelled', $html);
    }

    public function test_respects_reduced_motion(): void
    {
        $html = $this->get('/catalog')->assertOk()->getContent();

        $this->assertStringContainsString('prefers-reduced-motion', $html);
    }
}
