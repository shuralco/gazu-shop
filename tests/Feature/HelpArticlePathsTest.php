<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Шляхи в довідці мусять збігатися з реальним меню адмінки.
 *
 * Приклад із життя: стаття вела «Вигляд → SEO / тексти», а групи «Вигляд»
 * у меню немає взагалі (потрібно «Налаштування»). Клієнт шукав розділ і не
 * знаходив — при цьому права в нього були повні. Такі розбіжності мусить
 * ловити тест, а не лист від клієнта.
 */
class HelpArticlePathsTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** Назви пунктів і груп меню, як їх бачить адміністратор. */
    private function knownMenuLabels(): array
    {
        $panel = \Filament\Facades\Filament::getPanel('admin');
        $known = [];

        foreach ([...$panel->getPages(), ...$panel->getResources()] as $c) {
            try {
                $known[mb_strtolower((string) $c::getNavigationLabel())] = true;
                if ($g = $c::getNavigationGroup()) {
                    $known[mb_strtolower((string) $g)] = true;
                }
            } catch (\Throwable) {
                // сторінки з обовʼязковими параметрами пропускаємо
            }
        }

        return $known;
    }

    public function test_every_help_path_points_to_an_existing_menu_item(): void
    {
        $this->seed(\Database\Seeders\HelpArticlesSeeder::class);

        $known = $this->knownMenuLabels();
        $this->assertNotEmpty($known, 'меню адмінки не зчиталось');

        $bad = [];
        foreach (\App\Models\HelpArticle::all() as $article) {
            // «**Розділ → …**» на початку жирного фрагмента
            preg_match_all('/\*\*([^*\n]{2,40}?)\s*→/u', (string) $article->content, $m);

            foreach ($m[1] as $segment) {
                $label = mb_strtolower(trim($segment, " «»"));
                // прозу з стрілками («— це «АБО»: …») пропускаємо
                if ($label === '' || str_starts_with($label, '—')) {
                    continue;
                }
                if (! isset($known[$label])) {
                    $bad[] = "«{$segment}» у статті «{$article->title}»";
                }
            }
        }

        $this->assertSame([], $bad, "у довідці шляхи, яких немає в меню:\n".implode("\n", $bad));
    }
}
