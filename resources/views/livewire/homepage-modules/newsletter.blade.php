{{-- Newsletter Module --}}
@php
    $nlTitle = $module->getTranslatedSetting('title', 'newsletter_title_default', 'ПІДПИШІТЬСЯ НА РОЗСИЛКУ');
    $nlDescription = $module->getTranslatedSetting('description', 'newsletter_description_default', 'ОТРИМУЙТЕ ЕКСКЛЮЗИВНІ ПРОПОЗИЦІЇ ТА ЗНИЖКИ');
    $nlButtonText = $module->getTranslatedSetting('button_text', 'newsletter_button_default', 'ПІДПИСАТИСЯ');
@endphp

<section class="py-16 md:py-24 bg-black">
    <div class="max-w-screen-lg mx-auto px-4 md:px-8 text-center">
        @if($nlTitle)
            <h2 class="text-3xl md:text-6xl font-black text-white mb-6 md:mb-8">{{ $nlTitle }}</h2>
        @endif

        @if($nlDescription)
            <p class="text-lg md:text-2xl text-white mb-8 md:mb-12 font-medium">
                {{ $nlDescription }}
            </p>
        @endif

        <div class="max-w-xl mx-auto">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="email" placeholder="{{ __('general.newsletter_email_placeholder') }}"
                       class="flex-1 px-6 py-4 bg-white text-black border-2 border-white text-lg font-medium"
                       aria-label="{{ __('general.newsletter_email_aria') }}">
                <button class="btn-white px-8 py-4">{{ $nlButtonText }}</button>
            </div>
            <p class="text-sm text-gray-400 mt-4">
                {{ __('general.newsletter_consent') }}
            </p>
        </div>
    </div>
</section>
