{{-- Довантаження наступної сторінки каталогу.
     Режим береться з адмінки (GAZU візуальні блоки → Каталог):
       classic  — блок не рендериться взагалі, працює звичайна нумерація;
       more     — кнопка «Показати ще»;
       infinite — та сама кнопка + автоспрацювання, коли користувач докрутив.

     Тут лише розмітка — поведінка в assets/js/gazu-fx.js (initCatalogLoadMore),
     бо інлайн-скрипт не переживає SPA-заміну <main>.

     Кнопка — справжнє <a href> на наступну сторінку: без JS вона працює як
     звичайне посилання, а пошуковик бачить прохідний лінк. data-no-spa — щоб
     глобальний SPA-перехоплювач не переходив на сторінку замість довантаження.
     Нумеровану пагінацію ховає скрипт, тож у no-JS вона лишається робочою. --}}
@php($mode = $mode ?? 'classic')
@if($mode !== 'classic')
    <div id="gazu-load-more"
         data-mode="{{ $mode }}"
         data-next="{{ $paginator->hasMorePages() ? $paginator->nextPageUrl() : '' }}"
         data-total="{{ $paginator->total() }}"
         class="flex flex-col items-center gap-2 py-8">
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               data-no-spa
               data-gazu-load-more-btn
               class="gazu-btn-outline no-underline min-w-[220px] text-center">
                Показати ще
            </a>
        @endif
        <span data-gazu-load-more-status class="text-xs text-[var(--gazu-graphite)]">
            Показано {{ $paginator->count() }} з {{ $paginator->total() }}
        </span>
    </div>
@endif
