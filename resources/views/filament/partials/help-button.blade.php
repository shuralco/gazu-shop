@php
    // Контекстна кнопка довідки: за поточним admin-розділом (admin/{seg}) шукаємо
    // статтю з match_path і ведемо одразу на неї; інакше — на загальну довідку.
    $helpUrl = url('/admin/help');
    try {
        $seg = request()->segment(2); // admin/{seg}/...
        if ($seg && $seg !== 'help' && \Illuminate\Support\Facades\Schema::hasTable('help_articles')) {
            $slug = \App\Models\HelpArticle::query()->where('is_active', true)
                ->where('match_path', $seg)->value('slug');
            if ($slug) {
                $helpUrl = url('/admin/help?topic='.$slug);
            }
        }
    } catch (\Throwable $e) {
        // лишаємо загальний URL
    }
@endphp
<a href="{{ $helpUrl }}"
   title="Інструкції / довідка по розділу"
   class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-primary-400">
    <x-filament::icon icon="heroicon-o-question-mark-circle" class="h-5 w-5" />
    <span class="hidden md:inline">Довідка</span>
</a>
