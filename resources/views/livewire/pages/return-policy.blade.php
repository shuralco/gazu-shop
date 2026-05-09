<div class="pt-4 md:pt-6">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 mb-16">
        <x-breadcrumbs :items="[['title' => 'Повернення та обмін']]" />

        <h1 class="text-3xl md:text-5xl font-black mb-8 border-b-4 border-black pb-4">ПОВЕРНЕННЯ ТА ОБМІН</h1>

        <div class="space-y-8 text-base leading-relaxed">
            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">1. Право на повернення</h2>
                <p>Відповідно до Закону України "Про захист прав споживачів", ви маєте право повернути товар належної якості протягом 14 днів з моменту отримання, не враховуючи день покупки. Товар неналежної якості може бути повернений протягом гарантійного терміну.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">2. Умови повернення</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Товар не був у використанні та має товарний вигляд</li>
                    <li>Збережені оригінальна упаковка, ярлики та етикетки</li>
                    <li>Наявний документ, що підтверджує покупку (чек або номер замовлення)</li>
                    <li>Збережені споживчі властивості товару</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">3. Товари, що не підлягають поверненню</h2>
                <p>Відповідно до Постанови КМУ No 172, поверненню не підлягають:</p>
                <ul class="list-disc pl-6 space-y-1 mt-2">
                    <li>Білизна, панчішно-шкарпеткові вироби</li>
                    <li>Парфумерно-косметична продукція</li>
                    <li>Товари в аерозольній упаковці</li>
                    <li>Рукавички, дитячі іграшки м'які</li>
                    <li>Інші товари згідно з переліком, затвердженим КМУ</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">4. Процедура повернення</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="border-2 border-black p-4">
                        <div class="font-black text-lg mb-2">КРОК 1</div>
                        <p>Зв'яжіться з нами за телефоном {{ shopPhone() }} або email {{ shopEmail() }} та повідомте про бажання повернути товар.</p>
                    </div>
                    <div class="border-2 border-black p-4">
                        <div class="font-black text-lg mb-2">КРОК 2</div>
                        <p>Отримайте підтвердження та інструкції щодо відправки товару назад.</p>
                    </div>
                    <div class="border-2 border-black p-4">
                        <div class="font-black text-lg mb-2">КРОК 3</div>
                        <p>Відправте товар поштовою службою на вказану адресу. Вартість зворотної доставки за рахунок покупця (крім випадків неналежної якості).</p>
                    </div>
                    <div class="border-2 border-black p-4">
                        <div class="font-black text-lg mb-2">КРОК 4</div>
                        <p>Після отримання та перевірки товару ми повернемо кошти протягом 3-14 робочих днів на вашу банківську картку.</p>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">5. Терміни повернення коштів</h2>
                <p>Повернення коштів здійснюється на банківську картку, з якої була здійснена оплата, протягом 3-14 робочих днів з моменту отримання та перевірки поверненого товару. При оплаті накладеним платежем повернення здійснюється на банківські реквізити покупця.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">6. Гарантія</h2>
                <p>На всі товари поширюється гарантія виробника. Гарантійний термін вказується на сторінці товару та в гарантійному талоні. У разі виявлення дефекту зверніться до нас для організації гарантійного обслуговування.</p>
            </section>

            <section>
                <h2 class="text-xl font-black uppercase mb-3 border-b-2 border-black pb-2">7. Контакти</h2>
                <p><strong>Email:</strong> {{ shopEmail() }}</p>
                <p><strong>Телефон:</strong> {{ shopPhone() }}</p>
            </section>
        </div>
    </div>
</div>
