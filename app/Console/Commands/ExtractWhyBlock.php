<?php

namespace App\Console\Commands;

use App\Models\DisplaySetting;
use Illuminate\Console\Command;

/**
 * Переносить «Чому обирають GAZU» зі SEO-тексту в окремий блок переваг.
 *
 * Раніше ці переваги були підзаголовком усередині SEO-простирадла в самому
 * низу головної — їх бачив лише той, хто докрутив донизу. Тепер це окрема
 * секція під категоріями, тож текст треба один раз перенести.
 *
 * Команда ідемпотентна: якщо блок переваг уже заповнений або заголовка в
 * SEO-тексті немає, вона нічого не змінює.
 */
class ExtractWhyBlock extends Command
{
    protected $signature = 'gazu:extract-why {--dry-run : показати, що буде перенесено, нічого не змінюючи}';

    protected $description = 'Переносить «Чому обирають GAZU» зі SEO-блоку в окрему секцію переваг';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $why = trim((string) DisplaySetting::get('gazu_why_html', ''));
        if ($why !== '') {
            $this->info('Блок переваг уже заповнений — нічого не змінюю.');

            return self::SUCCESS;
        }

        $seo = (string) DisplaySetting::get('gazu_seo_html', '');
        if (trim($seo) === '') {
            $this->warn('SEO-текст порожній — переносити нічого.');

            return self::SUCCESS;
        }

        // Шукаємо заголовок «Чому обирають …» і забираємо все до наступного
        // заголовка. Усередині заголовка можуть бути теги (редактор лишає
        // «<h3><br>Чому обирають GAZU</h3>»), тож звіряємо ТЕКСТ, а не розмітку.
        $pattern = '~<h([23])[^>]*>(.*?)</h\1>(.*?)(?=<h[23][^>]*>|$)~isu';

        $m = null;
        if (preg_match_all($pattern, $seo, $all, PREG_SET_ORDER)) {
            foreach ($all as $block) {
                $text = trim(html_entity_decode(strip_tags($block[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if (preg_match('~^Чому\s+обирают[ьи]~iu', $text)) {
                    $m = [0 => $block[0], 1 => $block[1], 2 => $block[3], 'title' => $text];
                    break;
                }
            }
        }

        if ($m === null) {
            $this->warn('У SEO-тексті немає заголовка «Чому обирають …» — нічого не переношу.');

            return self::SUCCESS;
        }

        $title = $m['title'] !== '' ? $m['title'] : 'Чому обирають GAZU';
        $body = trim($m[2]);

        if ($body === '') {
            $this->warn('Під заголовком порожньо — залишаю SEO-текст як є.');

            return self::SUCCESS;
        }

        $newSeo = trim(str_replace($m[0], '', $seo));

        $this->line('Заголовок: '.$title);
        $this->line('Переноситься символів: '.mb_strlen($body));
        $this->line('SEO-текст скоротиться: '.mb_strlen($seo).' → '.mb_strlen($newSeo));

        if ($dry) {
            $this->info('Пробний запуск — нічого не змінено.');

            return self::SUCCESS;
        }

        DisplaySetting::set('gazu_why_title', $title);
        DisplaySetting::set('gazu_why_html', $body);
        DisplaySetting::set('gazu_seo_html', $newSeo);

        $this->info('Готово: переваги живуть окремою секцією, зі SEO-тексту прибрані.');

        return self::SUCCESS;
    }
}
