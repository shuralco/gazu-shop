<div class="pt-4 md:pt-6">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 mb-16">
        <x-breadcrumbs :items="[['title' => 'Публічна оферта']]" />

        <h1 class="text-3xl md:text-5xl font-black mb-8 border-b-4 border-black pb-4">ДОГОВІР ПУБЛІЧНОЇ ОФЕРТИ</h1>

        <div class="space-y-8 text-base leading-relaxed">
            <p class="text-sm text-gray-600">Дата оновлення: 28 березня 2026 року</p>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">1. Визначення термінів</h2>
                <ul class="space-y-2">
                    <li><strong>Продавець</strong> — інтернет-магазин {{ shopSetting('shop_name', 'SimpleShop') }}, що здійснює продаж товарів через сайт.</li>
                    <li><strong>Покупець</strong> — будь-яка дієздатна фізична або юридична особа, яка оформлює замовлення на сайті.</li>
                    <li><strong>Товар</strong> — продукція, представлена на сайті Продавця.</li>
                    <li><strong>Замовлення</strong> — належним чином оформлений запит Покупця на придбання Товару.</li>
                    <li><strong>Сайт</strong> — інтернет-ресурс за адресою {{ config('app.url') }}.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">2. Предмет договору</h2>
                <p>Продавець зобов'язується передати у власність Покупцю Товар, а Покупець зобов'язується оплатити та прийняти Товар на умовах цього Договору. Цей Договір є публічним відповідно до ст. 633 Цивільного кодексу України та є однаковим для всіх Покупців.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">3. Момент укладення договору</h2>
                <p>Оформлення Замовлення на Сайті є акцептом цієї оферти та означає повну згоду Покупця з усіма умовами Договору. Договір вважається укладеним з моменту отримання Покупцем підтвердження Замовлення на електронну пошту.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">4. Ціна та оплата</h2>
                <p>Ціни на Товари вказані на Сайті у гривнях (UAH) та включають ПДВ. Оплата здійснюється одним із способів: онлайн-оплата через LiqPay, WayForPay або Monobank; оплата при отриманні (накладений платіж). Покупець оплачує Товар за цінами, що діяли на момент оформлення Замовлення.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">5. Доставка</h2>
                <p>Доставка здійснюється на території України через Нову Пошту, УкрПошту або Meest Express. Терміни доставки: 1-3 робочі дні (Нова Пошта), 3-7 робочих днів (УкрПошта), 2-5 робочих днів (Meest Express). Ризик випадкової загибелі або пошкодження Товару переходить до Покупця з моменту передачі Товару перевізнику.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">6. Повернення</h2>
                <p>Повернення та обмін Товару здійснюється відповідно до Закону України "Про захист прав споживачів". Детальні умови: <a wire:navigate href="{{ locale_route('returns') }}" class="underline font-bold hover:no-underline">Повернення та обмін</a>.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">7. Персональні дані</h2>
                <p>Оформлюючи Замовлення, Покупець надає згоду на збір та обробку персональних даних відповідно до <a wire:navigate href="{{ locale_route('privacy') }}" class="underline font-bold hover:no-underline">Політики конфіденційності</a>.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">8. Вирішення спорів</h2>
                <p>Спори вирішуються шляхом переговорів. За неможливості досягнення згоди — у судовому порядку відповідно до законодавства України.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">9. Контакти Продавця</h2>
                <p><strong>Email:</strong> {{ shopEmail() }}</p>
                <p><strong>Телефон:</strong> {{ shopPhone() }}</p>
                <p><strong>Адреса:</strong> {{ shopSetting('shop_address', 'Київ, Україна') }}</p>
            </section>
        </div>
    </div>
</div>
