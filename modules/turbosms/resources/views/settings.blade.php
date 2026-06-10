<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Зберегти налаштування
            </x-filament::button>
        </div>
    </form>

    {{-- Журнал останніх відправок --}}
    <x-filament::section class="mt-8">
        <x-slot name="heading">Журнал відправок (останні 15)</x-slot>

        @if($this->recentMessages->isEmpty())
            <p class="text-sm text-gray-500">Ще нічого не відправлялось.</p>
        @else
            <div style="overflow-x:auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                            <th class="py-2 pr-4">Час</th>
                            <th class="py-2 pr-4">Телефон</th>
                            <th class="py-2 pr-4">Шаблон</th>
                            <th class="py-2 pr-4">Канал</th>
                            <th class="py-2 pr-4">Статус</th>
                            <th class="py-2">Текст / помилка</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->recentMessages as $m)
                            <tr class="border-b border-gray-100 align-top">
                                <td class="py-2 pr-4 whitespace-nowrap text-gray-500">{{ $m->created_at->format('d.m H:i') }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $m->phone }}</td>
                                <td class="py-2 pr-4"><x-filament::badge color="gray">{{ $m->template_key ?? 'ручна' }}</x-filament::badge></td>
                                <td class="py-2 pr-4">{{ $m->channel }}</td>
                                <td class="py-2 pr-4">
                                    <x-filament::badge :color="match($m->status){'sent','delivered','read'=>'success','failed','rejected'=>'danger',default=>'warning'}">
                                        {{ $m->status }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2 text-gray-600">
                                    {{ \Illuminate\Support\Str::limit($m->text, 70) }}
                                    @if($m->error)
                                        <div class="text-danger-600 text-xs mt-0.5">{{ $m->error }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
