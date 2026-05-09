{{-- Countdown Timer Module --}}
@php
    $endDate = $module->getSetting('end_date', now()->addDays(7)->format('Y-m-d H:i:s'));
    $cdTitle = $module->getTranslatedSetting('title', 'countdown_title_default', 'MEGA РОЗПРОДАЖ');
    $cdDescription = $module->getTranslatedSetting('description', 'countdown_description_default', 'Знижки до 70% на всі товари');
@endphp

<section class="py-16 md:py-24 bg-black text-white">
    <div class="max-w-screen-xl mx-auto px-4 md:px-8 text-center"
         x-data="countdown('{{ $endDate }}')"
         x-init="start()">

        @if($cdTitle)
            <h2 class="text-3xl md:text-6xl font-black mb-4 md:mb-6">{{ $cdTitle }}</h2>
        @endif

        @if($cdDescription)
            <p class="text-lg md:text-2xl font-medium mb-8 md:mb-12 opacity-80">{{ $cdDescription }}</p>
        @endif

        {{-- Timer --}}
        <div class="flex items-center justify-center gap-4 md:gap-8">
            <div class="text-center">
                <div class="w-20 h-20 md:w-28 md:h-28 border-4 border-white flex items-center justify-center">
                    <span class="text-3xl md:text-5xl font-black" x-text="days">00</span>
                </div>
                <p class="text-xs md:text-sm font-bold uppercase tracking-wider mt-3">{{ __('general.countdown_days') }}</p>
            </div>
            <span class="text-3xl md:text-5xl font-black opacity-50">:</span>
            <div class="text-center">
                <div class="w-20 h-20 md:w-28 md:h-28 border-4 border-white flex items-center justify-center">
                    <span class="text-3xl md:text-5xl font-black" x-text="hours">00</span>
                </div>
                <p class="text-xs md:text-sm font-bold uppercase tracking-wider mt-3">{{ __('general.countdown_hours') }}</p>
            </div>
            <span class="text-3xl md:text-5xl font-black opacity-50">:</span>
            <div class="text-center">
                <div class="w-20 h-20 md:w-28 md:h-28 border-4 border-white flex items-center justify-center">
                    <span class="text-3xl md:text-5xl font-black" x-text="minutes">00</span>
                </div>
                <p class="text-xs md:text-sm font-bold uppercase tracking-wider mt-3">{{ __('general.countdown_minutes') }}</p>
            </div>
            <span class="text-3xl md:text-5xl font-black opacity-50">:</span>
            <div class="text-center">
                <div class="w-20 h-20 md:w-28 md:h-28 border-4 border-white flex items-center justify-center">
                    <span class="text-3xl md:text-5xl font-black" x-text="seconds">00</span>
                </div>
                <p class="text-xs md:text-sm font-bold uppercase tracking-wider mt-3">{{ __('general.countdown_seconds') }}</p>
            </div>
        </div>
    </div>
</section>

@pushOnce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('countdown', (endDateStr) => ({
            days: '00',
            hours: '00',
            minutes: '00',
            seconds: '00',
            interval: null,
            endDate: null,

            start() {
                this.endDate = new Date(endDateStr).getTime()
                this.update()
                this.interval = setInterval(() => this.update(), 1000)
            },

            update() {
                const now = new Date().getTime()
                const distance = this.endDate - now

                if (distance < 0) {
                    clearInterval(this.interval)
                    this.days = '00'
                    this.hours = '00'
                    this.minutes = '00'
                    this.seconds = '00'
                    return
                }

                this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0')
                this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0')
                this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0')
                this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0')
            },

            destroy() {
                if (this.interval) clearInterval(this.interval)
            }
        }))
    })
</script>
@endPushOnce
