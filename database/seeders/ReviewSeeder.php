<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('is_active', true)->get();
        $users = User::all();

        if ($products->isEmpty()) {
            $this->command->error('Немає активних продуктів. Спочатку запустіть ProductSeeder.');

            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('Немає користувачів. Спочатку запустіть UserSeeder.');

            return;
        }

        // Українські імена для відгуків
        $reviewerNames = [
            'Олексій К.', 'Марія Д.', 'Андрій С.', 'Катерина П.', 'Володимир М.',
            'Наталія В.', 'Юрій Т.', 'Оксана Ш.', 'Дмитро Л.', 'Світлана Б.',
            'Михайло Г.', 'Ірина К.', 'Сергій П.', 'Тетяна Ж.', 'Роман Ч.',
            'Людмила Н.', 'Віктор З.', 'Олена Р.', 'Анатолій Ф.', 'Валентина Х.',
        ];

        // Позитивні відгуки (рейтинг 4-5)
        $positiveComments = [
            'Чудовий товар! Якість на висоті, доставка швидка. Рекомендую всім.',
            'Замовляла для подарунка, все прийшло в ідеальному стані. Упаковка дуже акуратна.',
            'Відмінна якість за таку ціну. Користуюся вже кілька місяців - все працює ідеально.',
            'Швидка доставка, гарна упаковка, товар відповідає опису. Дякую!',
            'Купував не перший раз в цьому магазині. Завжди висока якість та сервіс.',
            'Товар перевершив очікування! Рекомендую цей магазин друзям.',
            'Дуже задоволена покупкою. Якість відмінна, ціна адекватна.',
            'Швидко оформили замовлення, доставили в зазначений термін. Все супер!',
            'Оригінальний товар, швидка доставка. Обязательно буду замовляти ще.',
            'Відмінний сервіс! Консультанти допомогли з вибором, дуже вдячний.',
        ];

        // Нейтральні відгуки (рейтинг 3-4)
        $neutralComments = [
            'Товар хороший, але доставка затрималась на день. В цілому задоволений покупкою.',
            'Якість нормальна, але очікував трохи кращого за таку ціну.',
            'Доставили швидко, але упаковка була трохи пом\'ята. Товар цілий.',
            'Непоганий товар, але є деякі недоліки. За ціну - нормально.',
            'Замовив, отримав, працює. Ніяких особливих вражень.',
        ];

        // Критичні відгуки (рейтинг 1-2)
        $negativeComments = [
            'Товар не відповідає опису. Якість гірша ніж очікував.',
            'Доставка затрималась на тиждень, жодних вибачень від магазину.',
        ];

        $this->command->info('Створюємо відгуки для товарів...');

        $reviewCount = 0;

        foreach ($products as $product) {
            // Кількість відгуків для кожного товару (0-15)
            $reviewsPerProduct = rand(0, 15);

            if ($reviewsPerProduct === 0) {
                continue; // Деякі товари залишаємо без відгуків
            }

            for ($i = 1; $i <= $reviewsPerProduct; $i++) {
                // Випадковий користувач або анонімний відгук
                $useUser = rand(1, 100) <= 70; // 70% зареєстрованих користувачів
                $user = $useUser ? $users->random() : null;

                // Розподіл рейтингів: 70% позитивні (4-5), 25% нейтральні (3), 5% негативні (1-2)
                $ratingType = rand(1, 100);
                if ($ratingType <= 70) {
                    $rating = rand(4, 5);
                    $comment = $positiveComments[array_rand($positiveComments)];
                } elseif ($ratingType <= 95) {
                    $rating = 3;
                    $comment = $neutralComments[array_rand($neutralComments)];
                } else {
                    $rating = rand(1, 2);
                    $comment = $negativeComments[array_rand($negativeComments)];
                }

                $authorName = $user ? $user->name : $reviewerNames[array_rand($reviewerNames)];

                Review::create([
                    'user_id' => $user?->id,
                    'product_id' => $product->id,
                    'author_name' => $authorName,
                    'author_email' => $user?->email,
                    'rating' => $rating,
                    'comment' => $comment,
                    'is_verified_purchase' => $user ? rand(1, 100) <= 40 : false,
                    'status' => Review::STATUS_APPROVED,
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);

                $reviewCount++;
            }

            // Оновлюємо рейтинг товару
            $product->updateRatingFromReviews();
        }

        $this->command->info("✅ Створено {$reviewCount} відгуків для ".$products->count().' товарів');

        // Статистика по рейтингам
        $this->command->table(
            ['Рейтинг', 'Кількість відгуків'],
            [
                ['5 зірок', Review::where('rating', 5)->count()],
                ['4 зірки', Review::where('rating', 4)->count()],
                ['3 зірки', Review::where('rating', 3)->count()],
                ['2 зірки', Review::where('rating', 2)->count()],
                ['1 зірка', Review::where('rating', 1)->count()],
            ]
        );
    }
}
