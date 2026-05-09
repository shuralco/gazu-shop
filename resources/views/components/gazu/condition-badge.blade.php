@props(['value' => 'Новий'])
@php
    $map = [
        'Новий' => ['bg' => 'var(--gazu-success-bg)', 'c' => 'var(--gazu-success)'],
        'Б/у' => ['bg' => 'var(--gazu-warn-bg)', 'c' => 'var(--gazu-warn)'],
        'Відновл.' => ['bg' => 'var(--gazu-mist)', 'c' => 'var(--gazu-blue)'],
    ];
    $s = $map[$value] ?? $map['Новий'];
@endphp
<span class="text-[11px] gazu-mono px-2 py-0.5 rounded inline-flex items-center"
      style="background: {{ $s['bg'] }}; color: {{ $s['c'] }}; letter-spacing: 0.04em;">{{ $value }}</span>
