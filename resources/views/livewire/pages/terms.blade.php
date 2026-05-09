<div class="pt-4 md:pt-6">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 mb-16">
        <x-breadcrumbs :items="[['title' => 'Умови використання']]" />

        <h1 class="text-3xl md:text-5xl font-black mb-8 border-b-4 border-black pb-4">УМОВИ ВИКОРИСТАННЯ</h1>

        <div class="space-y-8 text-base leading-relaxed">
            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">1. Загальні положення</h2>
                <p>Ці Умови використання регулюють відносини між інтернет-магазином {{ shopSetting('shop_name', 'SimpleShop') }} (далі - Продавець) та будь-якою особою, яка використовує сайт для перегляду або придбання товарів (далі - Покупець). Оформлення замовлення на сайті означає повну згоду Покупця з цими Умовами.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">2. Оформлення замовлення</h2>
                <p>Покупець обирає товар на сайті, додає його до кошика та оформлює замовлення, вказуючи контактні дані та спосіб доставки. Після оформлення замовлення Покупець отримує підтвердження на електронну пошту. Продавець залишає за собою право зв'язатися з Покупцем для уточнення деталей замовлення.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">3. Ціни та оплата</h2>
                <p>Усі ціни на сайті вказані у гривнях (UAH) та включають ПДВ. Продавець залишає за собою право змінювати ціни без попереднього повідомлення. Ціна товару фіксується на момент оформлення замовлення. Оплата здійснюється через платіжні системи LiqPay, WayForPay, Monobank або готівкою при отриманні (де доступно).</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">4. Доставка</h2>
                <p>Доставка здійснюється службами Нова Пошта, УкрПошта та Meest Express. Терміни доставки залежать від обраної служби та регіону. Вартість доставки розраховується автоматично при оформленні замовлення. Безкоштовна доставка надається при замовленні від {{ shopSetting('free_shipping_threshold', '1500') }} грн.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">5. Повернення та обмін</h2>
                <p>Умови повернення та обміну товарів регулюються відповідним розділом на нашому сайті. Детальніше: <a wire:navigate href="{{ locale_route('returns') }}" class="underline font-bold hover:no-underline">Повернення та обмін</a>.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">6. Інтелектуальна власність</h2>
                <p>Усі матеріали сайту (тексти, зображення, логотипи, дизайн) є власністю Продавця та захищені законодавством про авторське право. Копіювання, розповсюдження або використання матеріалів сайту без письмової згоди Продавця заборонено.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">7. Відповідальність</h2>
                <p>Продавець не несе відповідальності за затримки доставки, спричинені діями третіх осіб (поштових служб), а також за тимчасову недоступність сайту через технічні роботи. Продавець гарантує відповідність товару опису на сайті.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">8. Вирішення спорів</h2>
                <p>Усі спори вирішуються шляхом переговорів. У разі неможливості досягнення згоди спір розглядається відповідно до законодавства України у судах за місцезнаходженням Продавця.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">9. Контакти</h2>
                <p><strong>Email:</strong> {{ shopEmail() }}</p>
                <p><strong>Телефон:</strong> {{ shopPhone() }}</p>
            </section>
        </div>
    </div>
</div>
