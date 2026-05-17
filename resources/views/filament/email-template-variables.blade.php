@if(empty($help))
    <div class="text-sm text-gray-500">У цього шаблону немає документованих змінних.</div>
@else
    <div class="space-y-2">
        @foreach($help as $item)
            <div class="flex items-start gap-3 text-sm">
                <code class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono shrink-0">&#123;&#123; {{ $item['key'] ?? '' }} &#125;&#125;</code>
                <span class="text-gray-600 dark:text-gray-400">{{ $item['desc'] ?? '' }}</span>
            </div>
        @endforeach
    </div>
@endif
