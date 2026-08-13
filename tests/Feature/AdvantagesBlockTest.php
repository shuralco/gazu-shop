<?php

namespace Tests\Feature;

use App\Models\DisplaySetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Блок «Чому обирають GAZU»: окрема секція під категоріями + одноразовий
 * перенос тексту зі старого SEO-простирадла.
 */
class AdvantagesBlockTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $seo = '<p>Вступ.</p><h3>Що ви знайдете</h3><p>Каталог.</p>'
        // саме так це зберіг редактор на проді — з <br> усередині заголовка
        .'<h3><br>Чому обирають GAZU</h3><ul><li><strong>Гарантія</strong> 12 місяців</li></ul>'
        .'<h3>Як підібрати</h3><p>За VIN.</p>';

    public function test_command_moves_block_out_of_seo_text(): void
    {
        DisplaySetting::set('gazu_seo_html', $this->seo);

        $this->artisan('gazu:extract-why')->assertExitCode(0);

        $why = (string) DisplaySetting::get('gazu_why_html');
        $seo = (string) DisplaySetting::get('gazu_seo_html');

        $this->assertStringContainsString('Гарантія', $why, 'переваги переїхали');
        $this->assertStringNotContainsString('Чому обирають', $seo, 'зі SEO-тексту прибрано');
        // Решта SEO-тексту мусить лишитись недоторканою
        $this->assertStringContainsString('Що ви знайдете', $seo);
        $this->assertStringContainsString('Як підібрати', $seo);
    }

    public function test_command_is_idempotent(): void
    {
        DisplaySetting::set('gazu_seo_html', $this->seo);
        $this->artisan('gazu:extract-why');
        $after = (string) DisplaySetting::get('gazu_why_html');

        $this->artisan('gazu:extract-why')->assertExitCode(0);

        $this->assertSame($after, (string) DisplaySetting::get('gazu_why_html'), 'повторний запуск нічого не дублює');
    }

    public function test_dry_run_changes_nothing(): void
    {
        DisplaySetting::set('gazu_seo_html', $this->seo);

        $this->artisan('gazu:extract-why', ['--dry-run' => true])->assertExitCode(0);

        $this->assertSame('', (string) DisplaySetting::get('gazu_why_html', ''));
        $this->assertStringContainsString('Чому обирають', (string) DisplaySetting::get('gazu_seo_html'));
    }

    public function test_section_renders_on_homepage(): void
    {
        DisplaySetting::set('gazu_why_enabled', true);
        DisplaySetting::set('gazu_why_title', 'Чому обирають GAZU');
        DisplaySetting::set('gazu_why_html', '<ul><li><strong>Гарантія</strong> 12 місяців</li></ul>');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Чому обирають GAZU', $html);
        $this->assertStringContainsString('gazu-advantages', $html);
    }

    public function test_section_hidden_when_empty(): void
    {
        DisplaySetting::set('gazu_why_html', '');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('gazu-advantages', $html, 'порожній блок не показуємо');
    }
}
