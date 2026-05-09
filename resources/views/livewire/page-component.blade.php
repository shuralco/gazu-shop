@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description ?: $page->excerpt)
@section('meta_keywords', $page->meta_keywords)
@section('robots', $page->getRobotsDirective())

@if($page->canonical_url)
    @section('canonical', $page->canonical_url)
@endif

@if($page->og_image)
    @section('og_image', $page->og_image)
@endif
@section('og_type', $page->og_type ?? 'article')

<div class="pt-4 md:pt-6">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 mb-16">
        <x-breadcrumbs :items="[['title' => $page->title]]" />

        @if($page->layout === 'full')
            {{-- Full width layout --}}
            <h1 class="text-3xl md:text-5xl font-black mb-8 border-b-4 border-black pb-4 dark:border-white">
                {{ mb_strtoupper($page->title) }}
            </h1>

            @if($page->excerpt)
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">{{ $page->excerpt }}</p>
            @endif

            <div class="prose prose-lg max-w-none dark:prose-invert
                        prose-headings:font-black prose-headings:uppercase
                        prose-h2:text-xl prose-h2:border-b-2 prose-h2:border-black prose-h2:pb-2 prose-h2:dark:border-white
                        prose-a:text-black prose-a:underline prose-a:dark:text-white
                        prose-img:border-4 prose-img:border-black prose-img:dark:border-white">
                {!! $page->content !!}
            </div>

        @elseif($page->layout === 'sidebar-left')
            {{-- Sidebar left layout --}}
            <div class="flex flex-col md:flex-row gap-8">
                <aside class="w-full md:w-72 shrink-0">
                    <div class="border-4 border-black dark:border-white p-4">
                        <h2 class="font-black text-lg uppercase mb-4 border-b-2 border-black dark:border-white pb-2">{{ __('general.navigation') }}</h2>
                        @php
                            $sidebarPages = \App\Models\Page::menu()->get();
                        @endphp
                        <nav class="space-y-2">
                            @foreach($sidebarPages as $navPage)
                                <a wire:navigate href="{{ $navPage->getUrl() }}"
                                   class="block py-1 font-bold hover:underline {{ $navPage->id === $page->id ? 'text-black dark:text-white underline' : 'text-gray-600 dark:text-gray-400' }}">
                                    @if($navPage->icon)
                                        <x-dynamic-component :component="'heroicon-o-' . $navPage->icon" class="w-4 h-4 inline mr-1" />
                                    @endif
                                    {{ $navPage->title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>

                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-5xl font-black mb-8 border-b-4 border-black pb-4 dark:border-white">
                        {{ mb_strtoupper($page->title) }}
                    </h1>

                    @if($page->excerpt)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">{{ $page->excerpt }}</p>
                    @endif

                    <div class="prose prose-lg max-w-none dark:prose-invert
                                prose-headings:font-black prose-headings:uppercase
                                prose-h2:text-xl prose-h2:border-b-2 prose-h2:border-black prose-h2:pb-2 prose-h2:dark:border-white
                                prose-a:text-black prose-a:underline prose-a:dark:text-white
                                prose-img:border-4 prose-img:border-black prose-img:dark:border-white">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>

        @elseif($page->layout === 'sidebar-right')
            {{-- Sidebar right layout --}}
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl md:text-5xl font-black mb-8 border-b-4 border-black pb-4 dark:border-white">
                        {{ mb_strtoupper($page->title) }}
                    </h1>

                    @if($page->excerpt)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">{{ $page->excerpt }}</p>
                    @endif

                    <div class="prose prose-lg max-w-none dark:prose-invert
                                prose-headings:font-black prose-headings:uppercase
                                prose-h2:text-xl prose-h2:border-b-2 prose-h2:border-black prose-h2:pb-2 prose-h2:dark:border-white
                                prose-a:text-black prose-a:underline prose-a:dark:text-white
                                prose-img:border-4 prose-img:border-black prose-img:dark:border-white">
                        {!! $page->content !!}
                    </div>
                </div>

                <aside class="w-full md:w-72 shrink-0">
                    <div class="border-4 border-black dark:border-white p-4">
                        <h2 class="font-black text-lg uppercase mb-4 border-b-2 border-black dark:border-white pb-2">{{ __('general.navigation') }}</h2>
                        @php
                            $sidebarPages = \App\Models\Page::menu()->get();
                        @endphp
                        <nav class="space-y-2">
                            @foreach($sidebarPages as $navPage)
                                <a wire:navigate href="{{ $navPage->getUrl() }}"
                                   class="block py-1 font-bold hover:underline {{ $navPage->id === $page->id ? 'text-black dark:text-white underline' : 'text-gray-600 dark:text-gray-400' }}">
                                    @if($navPage->icon)
                                        <x-dynamic-component :component="'heroicon-o-' . $navPage->icon" class="w-4 h-4 inline mr-1" />
                                    @endif
                                    {{ $navPage->title }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</div>
