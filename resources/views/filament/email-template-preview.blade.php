<div class="space-y-3">
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-sm">
            <strong>Subject:</strong> {{ $subject }}
        </div>
        <div class="bg-white dark:bg-gray-900 p-4 text-sm leading-relaxed prose dark:prose-invert max-w-none">
            {!! $body !!}
        </div>
    </div>
    <p class="text-xs text-gray-500">Preview з тестовими даними. Реальні значення підставляться при відправці.</p>
</div>
