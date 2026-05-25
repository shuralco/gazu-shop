<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kolirt\Ukrposhta\Facade\Ukrposhta;

class UpdateCityPostcodesMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ukrposhta:update-city-map {city_id} {--verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Оновити карту міських поштових індексів для конкретного міста УкрПошти';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cityId = (int) $this->argument('city_id');
        $verify = $this->option('verify');

        $this->info("Оновлення карти індексів для міста ID: {$cityId}");

        try {
            // Отримуємо всі відділення для міста
            $this->info('Отримання даних з API УкрПошти...');
            $offices = Ukrposhta::getPostOffices(null, null, $cityId);

            if (! $offices || count($offices) === 0) {
                $this->error("Не вдалося отримати дані для міста ID: {$cityId}");

                return 1;
            }

            $this->info('Отримано '.count($offices).' відділень');

            // Аналізуємо індекси
            $postcodes = [];
            $cityName = '';

            foreach ($offices as $office) {
                $officeArray = (array) $office;
                $postcode = $officeArray['POSTCODE'] ?? '';
                $city = $officeArray['CITY_UA'] ?? '';

                if (! $cityName && $city) {
                    $cityName = $city;
                }

                if ($postcode) {
                    $postcodes[] = $postcode;
                }
            }

            // Сортуємо індекси
            sort($postcodes);

            $this->info("Місто: {$cityName}");
            $this->info('Усі знайдені індекси ('.count($postcodes).'):');

            // Показуємо всі індекси для вибору
            foreach ($postcodes as $index => $postcode) {
                $this->line(($index + 1).". {$postcode}");
            }

            if ($verify) {
                // Режим верифікації - показуємо статистику
                $this->analyzePostcodes($postcodes, $cityName);

                return 0;
            }

            // Інтерактивний режим вибору міських індексів
            $this->newLine();
            $this->warn('УВАГА: Оберіть ТІЛЬКИ міські індекси!');
            $this->warn('Міські індекси зазвичай мають формат XXXXX, де останні цифри 00-20');
            $this->newLine();

            $selectedIndices = $this->ask('Введіть номери міських індексів через кому (наприклад: 1,3,5) або "all" для всіх:');

            if (strtolower($selectedIndices) === 'all') {
                $selectedPostcodes = $postcodes;
            } else {
                $indices = array_map('trim', explode(',', $selectedIndices));
                $selectedPostcodes = [];

                foreach ($indices as $index) {
                    $arrayIndex = (int) $index - 1;
                    if (isset($postcodes[$arrayIndex])) {
                        $selectedPostcodes[] = $postcodes[$arrayIndex];
                    }
                }
            }

            if (empty($selectedPostcodes)) {
                $this->error('Не обрано жодного індексу');

                return 1;
            }

            $this->info('Обрані міські індекси:');
            foreach ($selectedPostcodes as $postcode) {
                $this->line("- {$postcode}");
            }

            // Підтвердження
            if (! $this->confirm('Додати ці індекси до карти?')) {
                $this->info('Операцію скасовано');

                return 0;
            }

            // Генеруємо код для додавання в карту
            $codeToAdd = $this->generateMapCode($cityId, $selectedPostcodes, $cityName);

            $this->newLine();
            $this->info('Додайте наступний код до файлу CityPostcodesMap.php:');
            $this->newLine();
            $this->line($codeToAdd);

            $this->newLine();
            $this->info('✅ Готово! Не забудьте додати код до файлу CityPostcodesMap.php');

        } catch (\Exception $e) {
            $this->error('Помилка: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Аналізує індекси для надання рекомендацій
     */
    protected function analyzePostcodes(array $postcodes, string $cityName): void
    {
        $this->newLine();
        $this->info("=== АНАЛІЗ ІНДЕКСІВ ДЛЯ {$cityName} ===");

        // Групуємо за першими 3 цифрами
        $grouped = [];
        foreach ($postcodes as $postcode) {
            $prefix = substr($postcode, 0, 3);
            if (! isset($grouped[$prefix])) {
                $grouped[$prefix] = [];
            }
            $grouped[$prefix][] = $postcode;
        }

        $this->info("\nГрупи за першими 3 цифрами:");
        arsort($grouped);
        foreach ($grouped as $prefix => $codes) {
            $this->line("Префікс {$prefix}: ".count($codes).' індексів');
        }

        // Аналізуємо потенційні міські індекси
        $potentialUrban = [];
        foreach ($postcodes as $postcode) {
            if (strlen($postcode) === 5) {
                $lastTwo = substr($postcode, -2);
                if (is_numeric($lastTwo) && intval($lastTwo) <= 20) {
                    $potentialUrban[] = $postcode;
                }
            }
        }

        $this->newLine();
        $this->info('Потенційні міські індекси (закінчуються на 00-20):');
        foreach ($potentialUrban as $postcode) {
            $this->line("- {$postcode}");
        }

        $this->newLine();
        $this->warn('Рекомендація: Перевірте офіційні джерела УкрПошти для підтвердження міських індексів');
    }

    /**
     * Генерує код для додавання в карту
     */
    protected function generateMapCode(int $cityId, array $postcodes, string $cityName): string
    {
        $postcodesStr = "'".implode("', '", $postcodes)."'";

        return "// {$cityName}\n{$cityId} => [\n    {$postcodesStr},\n],";
    }
}
