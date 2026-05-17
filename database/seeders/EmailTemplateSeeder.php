<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key' => 'order.created',
                'name' => 'Замовлення створено (клієнт)',
                'subject' => 'Дякуємо за замовлення №{{order.id}} — GAZU',
                'body_html' => <<<'HTML'
<p>Доброго дня, {{order.customer_name}}!</p>
<p>Ваше замовлення <strong>№{{order.id}}</strong> прийнято.</p>
<p><strong>Сума:</strong> {{order.total}} ₴<br>
<strong>Доставка:</strong> {{order.delivery_method}}</p>
<p>Менеджер зв'яжеться з вами протягом 30 хвилин у робочий час (Пн-Нд 8:00–20:00).</p>
<p>Деталі замовлення: <a href="{{order.url}}">{{order.url}}</a></p>
<p>Дякуємо, що обрали GAZU!<br>0 800 75 10 24 — безкоштовно</p>
HTML,
                'to_kind' => EmailTemplate::TO_CUSTOMER,
                'variables_help' => [
                    ['key' => 'order.id', 'desc' => 'Номер замовлення'],
                    ['key' => 'order.customer_name', 'desc' => 'Ім\'я клієнта'],
                    ['key' => 'order.total', 'desc' => 'Сума у грн'],
                    ['key' => 'order.delivery_method', 'desc' => 'Спосіб доставки'],
                    ['key' => 'order.url', 'desc' => 'Посилання на сторінку замовлення'],
                ],
            ],
            [
                'key' => 'order.admin_new',
                'name' => 'Нове замовлення (адмін)',
                'subject' => 'НОВЕ замовлення №{{order.id}} · {{order.total}} ₴',
                'body_html' => <<<'HTML'
<p><strong>Нове замовлення №{{order.id}}</strong></p>
<p>Клієнт: {{order.customer_name}}<br>
Телефон: {{order.phone}}<br>
Email: {{order.email}}<br>
Доставка: {{order.delivery_method}} → {{order.delivery_city}}<br>
Сума: <strong>{{order.total}} ₴</strong></p>
<p><a href="{{order.admin_url}}">Відкрити в адмінці</a></p>
HTML,
                'to_kind' => EmailTemplate::TO_ADMIN,
                'variables_help' => [
                    ['key' => 'order.id', 'desc' => 'Номер'],
                    ['key' => 'order.customer_name', 'desc' => 'Клієнт'],
                    ['key' => 'order.phone', 'desc' => 'Телефон'],
                    ['key' => 'order.email', 'desc' => 'Email'],
                    ['key' => 'order.delivery_method', 'desc' => 'Доставка'],
                    ['key' => 'order.delivery_city', 'desc' => 'Місто'],
                    ['key' => 'order.total', 'desc' => 'Сума'],
                    ['key' => 'order.admin_url', 'desc' => 'Link на admin edit'],
                ],
            ],
            [
                'key' => 'order.paid',
                'name' => 'Замовлення оплачено (клієнт)',
                'subject' => 'Замовлення №{{order.id}} оплачено · GAZU',
                'body_html' => <<<'HTML'
<p>Доброго дня, {{order.customer_name}}!</p>
<p>Ми отримали вашу оплату по замовленню <strong>№{{order.id}}</strong>.</p>
<p>Найближчим часом передамо посилку у відправку. Ви отримаєте номер ТТН на email.</p>
<p>Дякуємо!<br>GAZU</p>
HTML,
                'to_kind' => EmailTemplate::TO_CUSTOMER,
                'variables_help' => [
                    ['key' => 'order.id', 'desc' => 'Номер'],
                    ['key' => 'order.customer_name', 'desc' => 'Клієнт'],
                ],
            ],
            [
                'key' => 'order.shipped',
                'name' => 'Замовлення відправлено (клієнт)',
                'subject' => 'Замовлення №{{order.id}} в дорозі · ТТН {{order.ttn}}',
                'body_html' => <<<'HTML'
<p>Доброго дня, {{order.customer_name}}!</p>
<p>Ваше замовлення <strong>№{{order.id}}</strong> передано до доставки.</p>
<p><strong>ТТН:</strong> {{order.ttn}}<br>
<strong>Перевізник:</strong> {{order.carrier}}</p>
<p>Відстежити: <a href="{{order.tracking_url}}">{{order.tracking_url}}</a></p>
<p>Очікувана дата доставки: {{order.expected_date}}</p>
HTML,
                'to_kind' => EmailTemplate::TO_CUSTOMER,
                'variables_help' => [
                    ['key' => 'order.id', 'desc' => 'Номер'],
                    ['key' => 'order.customer_name', 'desc' => 'Клієнт'],
                    ['key' => 'order.ttn', 'desc' => 'ТТН'],
                    ['key' => 'order.carrier', 'desc' => 'Перевізник (Нова Пошта/Укрпошта)'],
                    ['key' => 'order.tracking_url', 'desc' => 'Link на відстеження'],
                    ['key' => 'order.expected_date', 'desc' => 'Очікувана дата'],
                ],
            ],
            [
                'key' => 'callback.received',
                'name' => 'Заявка на дзвінок (адмін)',
                'subject' => 'НОВА заявка на дзвінок · {{callback.phone}}',
                'body_html' => <<<'HTML'
<p><strong>Нова заявка на дзвінок</strong></p>
<p>Телефон: <strong>{{callback.phone}}</strong><br>
Ім'я: {{callback.name}}<br>
Джерело: {{callback.source}}<br>
Сторінка: <a href="{{callback.referrer_url}}">{{callback.referrer_url}}</a><br>
Час: {{callback.created_at}}</p>
<p><a href="{{callback.admin_url}}">Відкрити в адмінці</a></p>
HTML,
                'to_kind' => EmailTemplate::TO_ADMIN,
                'variables_help' => [
                    ['key' => 'callback.phone', 'desc' => 'Телефон'],
                    ['key' => 'callback.name', 'desc' => 'Ім\'я'],
                    ['key' => 'callback.source', 'desc' => 'footer/product/etc'],
                    ['key' => 'callback.referrer_url', 'desc' => 'Звідки прийшов'],
                    ['key' => 'callback.created_at', 'desc' => 'Час'],
                    ['key' => 'callback.admin_url', 'desc' => 'Edit URL'],
                ],
            ],
            [
                'key' => 'auth.welcome',
                'name' => 'Реєстрація (клієнт)',
                'subject' => 'Ласкаво просимо в GAZU!',
                'body_html' => <<<'HTML'
<p>Доброго дня, {{user.name}}!</p>
<p>Ваш акаунт у GAZU створено успішно.</p>
<p>В кабінеті ви зможете:</p>
<ul>
  <li>Зберігати ваші авто в Гаражі — система буде пропонувати тільки сумісні запчастини</li>
  <li>Переглядати історію замовлень та статус доставки</li>
  <li>Додавати товари в Обране</li>
  <li>Використовувати бонусну програму та промокоди</li>
</ul>
<p><a href="{{site_url}}/kabinet">Перейти в кабінет</a></p>
HTML,
                'to_kind' => EmailTemplate::TO_CUSTOMER,
                'variables_help' => [
                    ['key' => 'user.name', 'desc' => 'Ім\'я'],
                    ['key' => 'user.email', 'desc' => 'Email'],
                    ['key' => 'site_url', 'desc' => 'Base URL магазину'],
                ],
            ],
        ];

        foreach ($defaults as $tpl) {
            EmailTemplate::updateOrCreate(['key' => $tpl['key']], $tpl);
        }
    }
}
