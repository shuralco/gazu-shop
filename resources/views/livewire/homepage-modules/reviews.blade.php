{{-- Reviews Module --}}
@php
    $limit = (int) $module->getSetting('limit', 6);
    $reviews = \App\Models\Review::query()
        ->where('status', \App\Models\Review::STATUS_APPROVED)
        ->with(['product:id,title,slug,image', 'user:id,name'])
        ->orderBy('created_at', 'desc')
        ->take($limit)
        ->get();
@endphp

@if($reviews->isNotEmpty())
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16 text-center">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reviews as $review)
                <div class="border-4 border-black p-6 bg-white">
                    {{-- Stars --}}
                    <div class="text-xl mb-3 tracking-wider">
                        {{ $review->stars }}
                    </div>

                    {{-- Comment --}}
                    <p class="text-base font-medium text-black leading-relaxed mb-4 line-clamp-4">
                        {{ $review->comment }}
                    </p>

                    {{-- Author & Product --}}
                    <div class="border-t-2 border-black pt-4 mt-auto">
                        <div class="font-black text-sm uppercase">
                            {{ $review->author_name ?? ($review->user->name ?? __('general.review_author_anonymous')) }}
                        </div>
                        @if($review->product)
                            <a wire:navigate href="{{ locale_url($review->product->slug) }}"
                               class="text-sm text-gray-600 hover:text-black font-medium mt-1 block">
                                {{ $review->product->title }}
                            </a>
                        @endif
                        <div class="text-xs text-gray-400 mt-1">
                            {{ $review->created_at->format('d.m.Y') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
