@php
    // L1 → L2 (групи) → L3 (підкатегорії з лічильниками)
    // У продакшні $megaTree приходить з GazuMenuComposer (Category::with('children.children')).
    // Тут залишаємо страховий fallback на випадок прямого include без composer.
    $megaTree = $megaTree ?? [
        ['id' => 'engine', 'label' => 'Двигун та системи', 'count' => 8420, 'groups' => [
            ['title' => 'Двигун', 'items' => [['Поршні та кільця', 412], ['Колінвал та шатуни', 186], ['Розподілвал', 240], ['Маховик', 124], ['Прокладка ГБЦ', 318], ['Сальники', 452], ['Опори двигуна', 286], ['Ремені та ролики', 624]]],
            ['title' => 'Система охолодження', 'items' => [['Радіатори', 312], ['Помпи', 268], ['Термостати', 196], ['Вентилятори', 142], ['Патрубки', 384], ['Бачки', 78]]],
            ['title' => 'Паливна система', 'items' => [['Форсунки', 322], ['Паливний насос', 184], ['ТНВД', 96], ['Регулятори тиску', 124], ['Свічки розжарювання', 218]]],
            ['title' => 'Випуск', 'items' => [['Глушники', 186], ['Каталізатори', 142], ['Лямбда-зонди', 312], ['Прокладки випуску', 224], ['Датчик EGR', 86]]],
        ]],
        ['id' => 'brakes', 'label' => 'Гальмівна система', 'count' => 2180, 'groups' => [
            ['title' => 'Передні гальма', 'items' => [['Колодки передні', 324], ['Диски передні', 284], ['Супорти передні', 142], ['Скоби супорта', 96]]],
            ['title' => 'Задні гальма', 'items' => [['Колодки задні', 286], ['Диски задні', 218], ['Барабани', 124]]],
            ['title' => 'Гідравліка', 'items' => [['Шланги гальмівні', 184], ['Трубки', 86], ['Головний циліндр', 124]]],
            ['title' => 'ABS / ESP', 'items' => [['Датчики ABS', 312], ['Блок ABS', 48], ['Гідроблок ESP', 24]]],
        ]],
        ['id' => 'suspension', 'label' => 'Підвіска та рульове', 'count' => 4120, 'groups' => [
            ['title' => 'Амортизатори', 'items' => [['Передні', 412], ['Задні', 386], ['Опори стійок', 224]]],
            ['title' => 'Пружини', 'items' => [['Пружини передні', 218], ['Пружини задні', 196], ['Стійки стабілізатора', 312]]],
            ['title' => 'Важелі', 'items' => [['Важелі передні', 286], ['Сайлентблоки', 412], ['Шарові опори', 196]]],
            ['title' => 'Рульове', 'items' => [['Рейки', 124], ['Тяги рульові', 218], ['Наконечники', 268]]],
        ]],
        ['id' => 'electric', 'label' => 'Електрика та електроніка', 'count' => 5860, 'groups' => [
            ['title' => 'Запуск та зарядка', 'items' => [['Стартери', 412], ['Генератори', 386], ['Реле-регулятори', 142]]],
            ['title' => 'Запалювання', 'items' => [['Свічки запалювання', 624], ['Котушки', 286], ['Високовольтні дроти', 184]]],
            ['title' => 'Датчики', 'items' => [['Кисню (лямбда)', 312], ['Колінвала', 218], ['ABS', 312]]],
            ['title' => 'АКБ та проводка', 'items' => [['Акумулятори', 142], ['Клеми', 218], ['Запобіжники', 96]]],
        ]],
        ['id' => 'body', 'label' => 'Кузов та оптика', 'count' => 2940, 'groups' => [
            ['title' => 'Зовнішній кузов', 'items' => [['Бампери', 346], ['Капоти', 96], ['Крила', 142], ['Двері', 86]]],
            ['title' => 'Скло', 'items' => [['Лобове', 124], ['Бічні', 186], ['Дзеркала', 218]]],
            ['title' => 'Освітлення', 'items' => [['Фари передні', 286], ['Лампи H4/H7', 412], ['LED', 196]]],
            ['title' => 'Кріплення', 'items' => [['Молдинги', 124], ['Кліпси', 286], ['Решітки', 96]]],
        ]],
        ['id' => 'interior', 'label' => 'Салон та комфорт', 'count' => 1240, 'groups' => [
            ['title' => 'Опорядження', 'items' => [['Килимки', 312], ['Чохли', 184]]],
            ['title' => 'Клімат', 'items' => [['Радіатор пічки', 84], ['Вентилятор салону', 124], ['Кондиціонер', 186]]],
            ['title' => 'Кермо', 'items' => [['Перемикачі', 124], ['Замки запалювання', 84]]],
        ]],
        ['id' => 'filters', 'label' => 'Фільтри', 'count' => 980, 'groups' => [
            ['title' => 'Фільтри', 'items' => [['Масляні', 312], ['Повітряні', 286], ['Паливні', 218], ['Салону', 164]]],
        ]],
        ['id' => 'oils', 'label' => 'Олива, хімія', 'count' => 1640, 'groups' => [
            ['title' => 'Моторні оливи', 'items' => [['5W-30', 286], ['5W-40', 312], ['10W-40', 184]]],
            ['title' => 'Трансмісійні', 'items' => [['АКПП', 184], ['МКПП', 142]]],
            ['title' => 'Хімія', 'items' => [['Антифриз', 186], ['Гальмівна', 124]]],
        ]],
        ['id' => 'tires', 'label' => 'Шини та диски', 'count' => 760, 'groups' => [
            ['title' => 'Шини', 'items' => [['Літо', 218], ['Зима', 286], ['Всесезон', 124]]],
            ['title' => 'Диски', 'items' => [['Литі', 96], ['Сталеві', 142]]],
        ]],
        ['id' => 'transmission', 'label' => 'Трансмісія', 'count' => 890, 'groups' => [
            ['title' => 'Зчеплення', 'items' => [['Комплекти', 184], ['Диски', 124], ['Корзини', 96]]],
            ['title' => 'Привод', 'items' => [['ШРУСи', 218], ['Пильовики ШРУСа', 184]]],
        ]],
        ['id' => 'lights', 'label' => 'Освітлення', 'count' => 1420, 'groups' => [
            ['title' => 'Зовнішнє', 'items' => [['Фари передні', 286], ['Фари задні', 224]]],
            ['title' => 'Лампи', 'items' => [['H4/H7', 412], ['LED', 196], ['Ксенон', 84]]],
        ]],
        ['id' => 'tools', 'label' => 'Інструмент', 'count' => 540, 'groups' => [
            ['title' => 'Ручний', 'items' => [['Ключі', 184], ['Знімачі', 96], ['Викрутки', 124]]],
            ['title' => 'Спецінструмент', 'items' => [['OBD діагностика', 86]]],
        ]],
    ];
    $brands = $brands ?? ['Bosch', 'SACHS', 'Lemförder', 'Febi', 'Mahle', 'NGK', 'Brembo', 'Mann', 'Continental'];
    $cars = $cars ?? [
        ['VW', 'Passat B8', '2014–2024'],
        ['Skoda', 'Octavia A7', '2013–2020'],
        ['Audi', 'A4 B9', '2015–2024'],
        ['BMW', '3 F30', '2012–2019'],
    ];
    $active = $activeMega ?? 'engine';
    $cat = collect($megaTree)->firstWhere('id', $active) ?? $megaTree[0];
    $totalCount = collect($megaTree)->sum('count');
@endphp

{{-- Popover container (positioned by parent in header.blade.php) --}}
<div class="bg-white rounded-xl overflow-hidden border border-[var(--gazu-line)] relative"
     x-data="{ activeMega: '{{ $megaTree[0]['id'] ?? 'engine' }}' }"
     style="box-shadow: 0 28px 60px -10px rgba(14,27,44,0.35), 0 8px 16px rgba(14,27,44,0.12);">

    {{-- Pointer arrow that visually connects popover to "Каталог" button --}}
    <div class="absolute -top-2 w-4 h-4 bg-white border-l border-t border-[var(--gazu-line)] rotate-45 z-10" style="left: 156px;"></div>

    {{-- Top strip --}}
    <div class="flex items-center gap-3 px-5 py-2.5 border-b border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase">Повний каталог</span>
        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)]">· {{ number_format($totalCount, 0, '.', ' ') }} товарів у {{ count($megaTree) }} категоріях</span>
        <span class="flex-1"></span>
        <button type="button" @click="megaOpen = false"
                class="w-7 h-7 border border-[var(--gazu-line)] bg-white rounded inline-flex items-center justify-center cursor-pointer text-[var(--gazu-graphite)]">
            <x-gazu.icon name="close" size="14"/>
        </button>
    </div>

    <div class="grid min-h-[540px]" style="grid-template-columns: 264px 1fr 260px;">

        {{-- L1 — root --}}
        <nav class="border-r border-[var(--gazu-line)] py-3.5 bg-[var(--gazu-paper)]">
            @foreach($megaTree as $c)
                @php $catLink = ! empty($c['slug']) ? route('gazu.catalog', ['cat' => $c['slug']]) : route('gazu.catalog'); @endphp
                <a href="{{ $catLink }}"
                   @mouseenter="activeMega = '{{ $c['id'] }}'"
                   :class="activeMega === '{{ $c['id'] }}' ? 'bg-white text-[var(--gazu-ink)] font-semibold' : 'text-[var(--gazu-graphite)]'"
                   :style="activeMega === '{{ $c['id'] }}' ? 'border-left:3px solid var(--gazu-blue)' : 'border-left:3px solid transparent'"
                   class="flex items-center gap-3 py-2.5 pr-3.5 pl-5 text-sm no-underline cursor-pointer relative">
                    <x-gazu.cat-icon kind="{{ $c['id'] }}" size="20"/>
                    <span class="flex-1 leading-tight">{{ $c['label'] }}</span>
                    <span class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-wider">{{ number_format($c['count'], 0, '.', ' ') }}</span>
                    <x-gazu.icon name="chevron" size="12" class="-rotate-90"/>
                </a>
            @endforeach
        </nav>

        {{-- L2+L3 — groups & subcategories per category, switched via Alpine --}}
        <div class="border-r border-[var(--gazu-line)]">
            @foreach($megaTree as $c)
                <div x-show="activeMega === '{{ $c['id'] }}'" x-cloak class="px-7 pt-5 pb-6 h-full">
                    <div class="flex items-center gap-3 mb-4 pb-3.5 border-b border-[var(--gazu-line)]">
                        <x-gazu.cat-icon kind="{{ $c['id'] }}" size="28"/>
                        <h3 class="gazu-display text-[22px] font-bold text-[var(--gazu-ink)] m-0">{{ $c['label'] }}</h3>
                        <span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase">{{ number_format($c['count'], 0, '.', ' ') }} товарів</span>
                        <a href="{{ ! empty($c['slug']) ? route('gazu.catalog', ['cat' => $c['slug']]) : route('gazu.catalog') }}" class="ml-auto text-[13px] text-[var(--gazu-blue)] no-underline inline-flex items-center gap-1">Усі →</a>
                    </div>
                    <div class="grid gap-x-7 gap-y-5" style="grid-template-columns: repeat({{ min(max(count($c['groups']), 1), 4) }}, 1fr);">
                        @foreach($c['groups'] as $g)
                            <div>
                                @if(!empty($g['title']))
                                    <div class="gazu-display text-sm font-bold text-[var(--gazu-ink)] mb-2.5">{{ $g['title'] }}</div>
                                @else
                                    <div class="mb-2.5" style="height: 21px;"></div>
                                @endif
                                <div class="flex flex-col gap-1.5">
                                    @foreach($g['items'] as [$name, $n])
                                        <a wire:navigate href="{{ route('gazu.catalog') }}" class="flex items-baseline gap-2 text-[13px] text-[var(--gazu-graphite)] no-underline hover:text-[var(--gazu-ink)]">
                                            <span class="flex-1">{{ $name }}</span>
                                            <span class="gazu-mono text-[10px] text-[var(--gazu-muted)]">{{ $n }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Col 3 — brands + cars + promo --}}
        <div class="px-5 pt-5 pb-6 flex flex-col gap-4.5" style="gap: 18px;">
            <div>
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-2.5">Топ бренди</div>
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach(array_slice($brands, 0, 9) as $b)
                        <div class="h-9 border border-[var(--gazu-line)] rounded flex items-center justify-center gazu-display text-[11px] font-semibold text-[var(--gazu-steel)] bg-white cursor-pointer">{{ $b }}</div>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="gazu-mono text-[10px] text-[var(--gazu-muted)] tracking-widest uppercase mb-2">Популярні авто</div>
                <div class="flex flex-col gap-0.5">
                    @foreach($cars as [$brand, $model, $years])
                        <a href="#" class="flex items-center gap-2 px-2 py-1.5 rounded no-underline text-[var(--gazu-ink)] text-xs">
                            <span class="flex-1">{{ $brand }} {{ $model }}</span>
                            <span class="gazu-mono text-[10px] text-[var(--gazu-muted)]">{{ $years }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-[var(--gazu-ink)] text-white rounded-lg p-4 flex flex-col gap-2 mt-auto">
                <div class="gazu-mono text-[9px] text-[var(--gazu-blue)] tracking-widest uppercase">Акція тижня</div>
                <div class="gazu-display text-lg font-bold">−20% оливи Castrol</div>
                <a href="#" class="self-start px-2.5 py-1.5 bg-[var(--gazu-blue)] text-white rounded text-xs font-medium no-underline mt-0.5">До акції →</a>
            </div>
        </div>
    </div>
</div>
