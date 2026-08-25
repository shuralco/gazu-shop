<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Ліміти розміру аплоаду мусять бути узгоджені по всьому ланцюжку.
 *
 * Регресія 25.08.2026: форма Filament обіцяла 5 MB, а nginx базового образу
 * рубав тіло на 1 MB. Користувач бачив лише «The data.image.… failed to upload»
 * — без натяку на причину; у логах nginx лежало 413 «client intended to send
 * too large body: 1836238 bytes». PHP додавав другий бар'єр (upload_max_filesize
 * за замовчуванням 2M), а Swoole — третій (package_max_length ~2M).
 *
 * Порядок мусить бути: форма < PHP upload < PHP post <= nginx <= Swoole.
 */
class UploadLimitsTest extends TestCase
{
    private function bytesFromIni(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $num = (int) $value;

        return match ($unit) {
            'g' => $num * 1024 ** 3,
            'm' => $num * 1024 ** 2,
            'k' => $num * 1024,
            default => $num,
        };
    }

    public function test_upload_limits_are_aligned_across_the_stack(): void
    {
        $formKb = (int) (preg_match(
            '/->maxSize\((\d+)\)/',
            File::get(app_path('Filament/Resources/ProductResource.php')),
            $m
        ) ? $m[1] : 0);
        $this->assertGreaterThan(0, $formKb, 'не знайдено maxSize() у формі товару');
        $form = $formKb * 1024;

        $ini = File::get(base_path('docker/php-opcache.ini'));
        preg_match('/^upload_max_filesize\s*=\s*(\S+)/m', $ini, $u);
        preg_match('/^post_max_size\s*=\s*(\S+)/m', $ini, $po);
        $upload = $this->bytesFromIni($u[1] ?? '0');
        $post = $this->bytesFromIni($po[1] ?? '0');

        preg_match('/client_max_body_size\s+(\S+);/', File::get(base_path('docker/nginx.conf')), $n);
        $nginx = $this->bytesFromIni(rtrim($n[1] ?? '0', ';'));

        $swoole = (int) (config('octane.swoole.options.package_max_length') ?? 0);

        $this->assertGreaterThan($form, $upload, 'PHP upload_max_filesize нижчий за ліміт форми');
        $this->assertGreaterThanOrEqual($upload, $post, 'post_max_size мусить бути >= upload_max_filesize');
        $this->assertGreaterThanOrEqual($post, $nginx, 'nginx рубатиме тіло раніше за PHP');
        $this->assertGreaterThanOrEqual($nginx, $swoole, 'Swoole обірве запит раніше за nginx');
    }
}
