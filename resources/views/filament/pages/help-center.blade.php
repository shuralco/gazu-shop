<x-filament-panels::page>
    @php
        $groups = $this->getGroupedArticles();
        $current = $this->getCurrentArticle();
    @endphp

    @if ($groups->isEmpty())
        <x-filament::section>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Статей довідки ще немає. Додайте їх у розділі «Статті довідки».
            </p>
        </x-filament::section>
    @else
        <div style="display:grid;gap:1.25rem;grid-template-columns:260px minmax(0,1fr);align-items:start">

            {{-- ─── Сайдбар тем ─── --}}
            <x-filament::section class="help-sidebar">
                <nav style="display:flex;flex-direction:column;gap:1rem">
                    @foreach ($groups as $section => $articles)
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1.5">{{ $section }}</div>
                            <div style="display:flex;flex-direction:column;gap:2px">
                                @foreach ($articles as $a)
                                    @php $active = $current && $current->id === $a->id; @endphp
                                    <a href="{{ url('/admin/help?topic='.$a->slug) }}"
                                       class="flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[13px] no-underline transition
                                              {{ $active
                                                 ? 'bg-primary-50 text-primary-700 font-semibold dark:bg-primary-500/10 dark:text-primary-300'
                                                 : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                                        @if ($a->icon)
                                            <x-filament::icon :icon="$a->icon" class="h-4 w-4 shrink-0" />
                                        @endif
                                        <span>{{ $a->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>
            </x-filament::section>

            {{-- ─── Стаття ─── --}}
            <x-filament::section>
                @if ($current)
                    <x-slot name="heading">
                        <span class="flex items-center gap-2">
                            @if ($current->icon)
                                <x-filament::icon :icon="$current->icon" class="h-5 w-5" />
                            @endif
                            {{ $current->title }}
                        </span>
                    </x-slot>

                    <article class="help-article">
                        {!! $current->content_html !!}
                    </article>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">Оберіть тему ліворуч.</p>
                @endif
            </x-filament::section>
        </div>

        {{-- Типографіка статті (незалежно від наявності @tailwindcss/typography) --}}
        <style>
            .help-article { font-size: 14px; line-height: 1.7; color: rgb(55 65 81); }
            .dark .help-article { color: rgb(209 213 219); }
            .help-article h1, .help-article h2, .help-article h3 { font-weight: 700; line-height: 1.25; margin: 1.4em 0 .5em; color: rgb(17 24 39); }
            .dark .help-article h1, .dark .help-article h2, .dark .help-article h3 { color: rgb(243 244 246); }
            .help-article h1 { font-size: 1.5rem; } .help-article h2 { font-size: 1.25rem; } .help-article h3 { font-size: 1.05rem; }
            .help-article h1:first-child, .help-article h2:first-child { margin-top: 0; }
            .help-article p { margin: .7em 0; }
            .help-article ul, .help-article ol { margin: .7em 0; padding-left: 1.4em; }
            .help-article ul { list-style: disc; } .help-article ol { list-style: decimal; }
            .help-article li { margin: .3em 0; }
            .help-article a { color: rgb(37 99 235); text-decoration: underline; }
            .help-article code { background: rgba(0,0,0,.06); padding: .1em .35em; border-radius: 4px; font-size: .9em; }
            .dark .help-article code { background: rgba(255,255,255,.1); }
            .help-article pre { background: rgba(0,0,0,.05); padding: 1em; border-radius: 8px; overflow:auto; }
            .dark .help-article pre { background: rgba(255,255,255,.06); }
            .help-article img { max-width: 100%; height: auto; border-radius: 10px; border: 1px solid rgba(0,0,0,.08); margin: 1em 0; box-shadow: 0 6px 20px -8px rgba(0,0,0,.18); }
            .help-article table { width: 100%; border-collapse: collapse; margin: 1em 0; font-size: 13px; }
            .help-article th, .help-article td { border: 1px solid rgba(0,0,0,.1); padding: .5em .7em; text-align: left; }
            .dark .help-article th, .dark .help-article td { border-color: rgba(255,255,255,.12); }
            .help-article th { background: rgba(0,0,0,.04); font-weight: 600; }
            .dark .help-article th { background: rgba(255,255,255,.05); }
            .help-article blockquote { border-left: 3px solid rgb(37 99 235); padding-left: 1em; margin: 1em 0; color: rgb(107 114 128); }
        </style>
    @endif
</x-filament-panels::page>
