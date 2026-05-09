{{-- Text Block Module --}}
@php
    $content = $module->getSetting('content', '');
@endphp

@if($content)
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-screen-xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-5xl font-black text-black mb-6 md:mb-8">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @endif

        <div class="border-4 border-black p-8 md:p-16">
            <div class="space-y-4 text-base md:text-lg font-medium text-black leading-relaxed prose prose-lg max-w-none">
                {!! $content !!}
            </div>
        </div>
    </div>
</section>
@endif
