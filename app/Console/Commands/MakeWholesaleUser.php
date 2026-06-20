<?php

namespace App\Console\Commands;

use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductGroupPrice;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Провіжинг гуртового тест-користувача для перевірки групових цін на вітрині.
 *
 * Приклад:
 *   php artisan gazu:wholesale-user opt@gazu.local SuperSecret123
 *   php artisan gazu:wholesale-user opt@gazu.local Pass --group="Дистриб'ютор" --product=14069 --price=160 --currency=USD
 *
 * Пароль задаєте ВИ (аргумент команди) — створюєте облікові дані самостійно.
 * Команда: створює/оновлює користувача, призначає групу клієнтів і гарантує,
 * що вказаний товар має гуртову ціну для цієї групи (щоб ефект був видимий).
 */
class MakeWholesaleUser extends Command
{
    protected $signature = 'gazu:wholesale-user
        {email : E-mail тест-користувача}
        {password : Пароль (задаєте ви)}
        {--name=Опт Тест : Імʼя}
        {--group=Оптовий : display_name або name групи клієнтів}
        {--discount=5 : %-знижка групи, якщо групу доводиться створити}
        {--product=14069 : ID товару для explicit гуртової ціни (0 = пропустити)}
        {--price=160 : Гуртова ціна товару}
        {--currency=USD : Валюта гуртової ціни}
        {--min=1 : Мінімальна кількість (min_quantity)}';

    protected $description = 'Створити/оновити гуртового тест-користувача + призначити групу + гарантувати гуртову ціну товару';

    public function handle(): int
    {
        // 1) Група клієнтів — знайти за display_name/name або створити.
        $groupName = (string) $this->option('group');
        $group = CustomerGroup::query()
            ->where('display_name', $groupName)
            ->orWhere('name', $groupName)
            ->first();

        if (! $group) {
            $group = CustomerGroup::create([
                'name' => \Illuminate\Support\Str::slug($groupName) ?: 'wholesale',
                'display_name' => $groupName,
                'discount_percentage' => (float) $this->option('discount'),
                'is_active' => true,
            ]);
            $this->info("Створено групу клієнтів: {$group->display_name} (-{$group->discount_percentage}%)");
        } else {
            $this->line("Група знайдена: {$group->display_name} (-{$group->discount_percentage}%)");
        }

        // 2) Користувач — створити або оновити (пароль із аргументу).
        $email = (string) $this->argument('email');
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->name ?: (string) $this->option('name');
        $user->password = Hash::make((string) $this->argument('password'));
        $user->customer_group_id = $group->id;
        if (\Schema::hasColumn('users', 'email_verified_at') && ! $user->email_verified_at) {
            $user->email_verified_at = now();
        }
        $user->save();
        $this->info(($user->wasRecentlyCreated ? 'Створено' : 'Оновлено')." користувача {$email} → група «{$group->display_name}»");

        // 3) Explicit гуртова ціна на товар (щоб ефект був явно видимий).
        $productId = (int) $this->option('product');
        if ($productId > 0 && ($product = Product::find($productId))) {
            $gp = ProductGroupPrice::firstOrNew([
                'product_id' => $product->id,
                'customer_group_id' => $group->id,
            ]);
            $gp->price = (float) $this->option('price');
            $gp->price_currency = (string) $this->option('currency');
            $gp->min_quantity = (int) $this->option('min');
            $gp->save();

            $uah = \App\Models\Currency::toBase($gp->price, $gp->price_currency);
            $this->info(sprintf(
                'Гуртова ціна на «%s»: %s %s (≈ %s грн) від %d шт',
                is_array($product->title) ? ($product->title['uk'] ?? $product->id) : $product->title,
                rtrim(rtrim(number_format($gp->price, 2, '.', ''), '0'), '.'),
                $gp->price_currency,
                number_format($uah, 0, '.', ' '),
                $gp->min_quantity
            ));
            $this->line('Перевірити: '.url('/'.($product->slug['uk'] ?? $product->slug ?? $product->id)));
        }

        $this->newLine();
        $this->info('Готово. Увійдіть на вітрині цим e-mail/паролем — побачите гуртові ціни.');

        return self::SUCCESS;
    }
}
