<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Switches the active storefront theme by rewriting the @import in
 * resources/css/app.css. Run `npm run build` after to materialize the
 * change in public/build.
 *
 * Usage:
 *   php artisan theme:use brutal
 *   php artisan theme:use auto-parts
 *
 * Reads available tokens from resources/css/tokens/*.css.
 */
class ThemeUseCommand extends Command
{
    protected $signature = 'theme:use
        {name? : Theme name (brutal | auto-parts | custom file in resources/css/tokens/)}
        {--list : List available themes and exit}';

    protected $description = 'Activate a storefront theme by swapping the tokens import in app.css.';

    public function handle(): int
    {
        $tokensDir = resource_path('css/tokens');
        $themes = collect(File::files($tokensDir))
            ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
            ->filter(fn ($n) => $n !== 'active')
            ->values()
            ->all();

        if ($this->option('list')) {
            $this->info('Available themes (resources/css/tokens):');
            foreach ($themes as $t) {
                $this->line('  - '.$t);
            }

            return self::SUCCESS;
        }

        $name = (string) $this->argument('name');
        if (! in_array($name, $themes, true)) {
            $this->error("Theme '{$name}' not found. Available: ".implode(', ', $themes));

            return self::FAILURE;
        }

        $appCss = resource_path('css/app.css');
        $css = File::get($appCss);

        $pattern = '/@import\s+\'\.\/tokens\/[a-z0-9-]+\.css\';/i';
        $replacement = "@import './tokens/{$name}.css';";

        if (! preg_match($pattern, $css)) {
            $this->error('Could not locate the tokens import line in app.css. Manual edit required.');

            return self::FAILURE;
        }

        $updated = preg_replace($pattern, $replacement, $css, 1);
        File::put($appCss, $updated);

        $this->info("Theme switched to: {$name}");
        $this->newLine();
        $this->warn('Next step:');
        $this->line('   npm run build  (or run vite dev) to rebuild CSS.');

        return self::SUCCESS;
    }
}
