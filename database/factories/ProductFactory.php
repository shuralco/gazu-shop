<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $ukrainianProducts = [
            'Електроніка' => [
                'iPhone 15 Pro Max 256GB',
                'Samsung Galaxy S24 Ultra',
                'MacBook Air M3 13"',
                'iPad Pro 12.9" M2',
                'AirPods Pro 2 покоління',
                'Apple Watch Series 9',
                'PlayStation 5 Slim',
                'Nintendo Switch OLED',
                'Xiaomi Mi Band 8',
                'Sony WH-1000XM5 навушники',
            ],
            'Одяг' => [
                'Футболка чоловіча бавовняна',
                'Джинси жіночі прямого крою',
                'Светр в\'язаний зимовий',
                'Куртка демісезонна',
                'Кросівки спортивні Nike',
                'Плаття літнє легке',
                'Штани спортивні Adidas',
                'Сорочка класична біла',
                'Спідниця міні джинсова',
                'Худі унісекс з капюшоном',
            ],
            'Дім і сад' => [
                'Пилосос робот Xiaomi',
                'Кавоварка еспресо',
                'Мультиварка 5л',
                'Праска парова',
                'Міксер планетарний',
                'Чайник електричний скляний',
                'Сковорода антипригарна 28см',
                'Постільна білизна сатин',
                'Рушник банний бамбук',
                'Подушка ортопедична',
            ],
            'Спорт' => [
                'Велосипед гірський 27.5"',
                'Гантелі набірні 2х20кг',
                'Мат для йоги TPE',
                'Протеїн сироватковий 2кг',
                'Кросівки для бігу',
                'Рюкзак туристичний 50л',
                'Палатка 3-місна',
                'Роликові ковзани',
                'М\'яч футбольний FIFA',
                'Бинти боксерські 4м',
            ],
            'Краса' => [
                'Крем для обличчя зволожуючий',
                'Шампунь для жирного волосся',
                'Маска для волосся відновлююча',
                'Туш для вій водостійка',
                'Помада рідка матова',
                'Тональний крем SPF30',
                'Сироватка з вітаміном C',
                'Скраб для тіла з солями',
                'Маска для обличчя глиняна',
                'Олія для волосся аргана',
            ],
        ];

        $ukrainianDescriptions = [
            'Високоякісний товар з гарантією 2 роки та безкоштовною доставкою',
            'Оригінальний продукт від офіційного дистриб\'ютора в Україні',
            'Сертифікований товар з європейськими стандартами якості',
            'Популярний вибір покупців з відмінними відгуками',
            'Інноваційний дизайн та надійність від перевіреного бренду',
            'Ексклюзивна пропозиція з обмеженою кількістю в наявності',
            'Преміум якість за доступною ціною для всієї родини',
            'Екологічно чистий продукт безпечний для здоров\'я',
            'Стильний та функціональний аксесуар для щоденного використання',
            'Професійна якість для домашнього та комерційного використання',
        ];

        $categories = \App\Models\Category::pluck('title', 'id')->toArray();
        if (empty($categories)) {
            $categoryTitle = 'Електроніка';
        } else {
            $categoryTitle = $categories[array_rand($categories)];
        }
        $categoryProducts = $ukrainianProducts[$categoryTitle] ?? $ukrainianProducts['Електроніка'];

        $title = $categoryProducts[array_rand($categoryProducts)];
        $name = explode(' ', $title)[0];

        return [
            'name' => $name,
            'title' => $title,
            'excerpt' => $ukrainianDescriptions[array_rand($ukrainianDescriptions)],
            'content' => 'Детальний опис товару з технічними характеристиками та специфікаціями. Товар має сертифікат якості та офіційну гарантію виробника. Швидка доставка по всій Україні службами Нова Пошта та УкрПошта.',
            'price' => $this->faker->randomFloat(2, 50, 5000),
            'old_price' => 0,
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'is_hit' => $this->faker->boolean(20),
            'is_new' => $this->faker->boolean(30),
            'is_active' => true,
            'quantity' => $this->faker->numberBetween(10, 100),
            'image' => null,
        ];
    }

    public function hit(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hit' => true,
        ]);
    }

    public function newProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_new' => true,
        ]);
    }

    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    public function inStock(?int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity ?? $this->faker->numberBetween(10, 100),
        ]);
    }
}
