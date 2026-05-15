<button type="button"
        data-trigger="{{ $level }}"
        @click="toggleLevel('{{ $level }}')"
        :disabled="triggerState('{{ $level }}').locked"
        :class="[
            openLevel === '{{ $level }}'           ? 'shadow-[inset_0_0_0_2px_var(--gazu-blue,#2563eb)]'
                                                    : (triggerState('{{ $level }}').selected ? 'shadow-[inset_0_0_0_1px_var(--gazu-line-2)]' : 'shadow-[inset_0_0_0_1px_var(--gazu-line)]'),
            triggerState('{{ $level }}').locked   ? 'opacity-60 cursor-not-allowed bg-[var(--gazu-paper)]' : 'cursor-pointer bg-white hover:shadow-[inset_0_0_0_1px_var(--gazu-line-2)]',
        ]"
        class="group w-full text-left px-3 py-3 rounded-lg transition-all flex items-center justify-between gap-2 min-h-[58px]">
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-1.5 mb-0.5">
            <span :class="triggerState('{{ $level }}').selected ? 'bg-[var(--gazu-success,#1f9d55)]' : 'bg-[var(--gazu-line-2)]'"
                  class="w-1.5 h-1.5 rounded-full transition-colors shrink-0"></span>
            <span class="text-[10px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)]">{{ $label }}</span>
        </div>
        <div class="text-[14px] font-medium leading-tight truncate"
             :class="triggerState('{{ $level }}').selected ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)]'"
             x-text="triggerState('{{ $level }}').label || '{{ $placeholderLocked }}'"></div>
    </div>
    <svg class="shrink-0 text-[var(--gazu-graphite)] transition-transform"
         :class="openLevel === '{{ $level }}' ? 'rotate-180' : ''"
         x-show="!triggerState('{{ $level }}').locked"
         width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    <svg class="shrink-0 text-[var(--gazu-graphite)] opacity-60"
         x-show="triggerState('{{ $level }}').locked"
         width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
</button>
