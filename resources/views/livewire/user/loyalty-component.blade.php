<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . $title }}</title>
        <meta name="description" content="Програма лояльності">
    @endsection

    @include('livewire.user.partials.brutal-styles')

    <div class="container py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                @include('livewire.user.partials.account-sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <h1 class="brutal-title">{{ $title }}</h1>

                <!-- Current Tier Card -->
                <div class="brutal-content-card" style="border-width: 6px; {{ $tier?->color ? 'border-color: ' . $tier->color . ';' : '' }}">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="text-muted fw-bold text-uppercase mb-1">Ваш рівень</p>
                            <h2 style="font-size: 48px; font-weight: 900; text-transform: uppercase; margin-bottom: 8px; {{ $tier?->color ? 'color: ' . $tier->color . ';' : '' }}">
                                {{ $tier?->display_name ?? 'Bronze' }}
                            </h2>
                            <p class="fw-bold mb-2" style="font-size: 20px;">
                                Баланс: <span style="font-size: 36px; font-weight: 900;">{{ number_format($user->loyalty_points) }}</span> балів
                            </p>
                            @if($redemptionValue > 0)
                                <p class="text-muted mb-0">
                                    Еквівалент: {{ formatPrice($redemptionValue) }} знижки
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-md-end">
                            @if($tier)
                                <div style="font-size: 14px; text-transform: uppercase; font-weight: 700;" class="mb-2">
                                    Множник балів
                                </div>
                                <div style="font-size: 48px; font-weight: 900;">
                                    x{{ number_format($tier->points_multiplier, 1) }}
                                </div>
                                @if($tier->discount_percentage > 0)
                                    <div class="mt-2" style="font-size: 14px; font-weight: 700; text-transform: uppercase;">
                                        Знижка: {{ number_format($tier->discount_percentage, 0) }}%
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Progress to Next Tier -->
                @if($nextTier)
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">Прогрес до наступного рівня</h2>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold" style="font-size: 16px;">{{ $tier?->display_name ?? 'Bronze' }}</span>
                        <span class="fw-bold" style="font-size: 16px; {{ $nextTier->color ? 'color: ' . $nextTier->color . ';' : '' }}">
                            {{ $nextTier->display_name }}
                        </span>
                    </div>
                    <div class="brutal-progress-bar mb-3">
                        <div class="brutal-progress-fill" style="width: {{ $progress }}%; {{ $nextTier->color ? 'background: ' . $nextTier->color . ';' : '' }}"></div>
                    </div>
                    <p class="text-muted mb-0">
                        {{ number_format($progress, 1) }}% -- потрібно ще {{ number_format($nextTier->min_points) }} балів для досягнення рівня {{ $nextTier->display_name }}
                    </p>
                </div>
                @endif

                <!-- All Tiers -->
                @if($allTiers->count() > 0)
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">Рівні програми</h2>
                    <div class="row g-3">
                        @foreach($allTiers as $t)
                        <div class="col-6 col-md-3">
                            <div class="brutal-tier-card {{ $tier && $tier->id === $t->id ? 'active' : '' }}"
                                 style="{{ $t->color ? 'border-color: ' . $t->color . ';' : '' }}
                                        {{ $tier && $tier->id === $t->id && $t->color ? 'box-shadow: 6px 6px 0 ' . $t->color . ';' : '' }}">
                                <div style="font-size: 14px; font-weight: 900; text-transform: uppercase; {{ $t->color ? 'color: ' . $t->color . ';' : '' }}">
                                    {{ $t->display_name }}
                                </div>
                                <div style="font-size: 12px; font-weight: 600;" class="text-muted mt-1">
                                    від {{ number_format($t->min_points) }} балів
                                </div>
                                <div style="font-size: 24px; font-weight: 900;" class="mt-2">
                                    x{{ number_format($t->points_multiplier, 1) }}
                                </div>
                                @if($t->discount_percentage > 0)
                                    <div style="font-size: 12px; font-weight: 700; text-transform: uppercase;" class="mt-1">
                                        -{{ number_format($t->discount_percentage, 0) }}%
                                    </div>
                                @endif
                                @if($tier && $tier->id === $t->id)
                                    <div style="background: black; color: white; padding: 2px 8px; font-size: 10px; font-weight: 900; text-transform: uppercase; display: inline-block;" class="mt-2">
                                        Ваш рівень
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Transactions History -->
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">Історія транзакцій</h2>

                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="brutal-table">
                                <thead>
                                    <tr>
                                        <th>Тип</th>
                                        <th>Бали</th>
                                        <th>Баланс</th>
                                        <th>Опис</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $tx)
                                    <tr wire:key="tx-{{ $tx->id }}">
                                        <td>
                                            @switch($tx->type)
                                                @case('earned')
                                                    <span style="background: #34c759; color: white; padding: 2px 10px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                        Нараховано
                                                    </span>
                                                    @break
                                                @case('spent')
                                                    <span style="background: #ff9500; color: white; padding: 2px 10px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                        Списано
                                                    </span>
                                                    @break
                                                @case('expired')
                                                    <span style="background: #ff3b30; color: white; padding: 2px 10px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                        Прострочено
                                                    </span>
                                                    @break
                                                @case('birthday')
                                                    <span style="background: #af52de; color: white; padding: 2px 10px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                        День народж.
                                                    </span>
                                                    @break
                                                @case('adjusted')
                                                    <span style="background: #007aff; color: white; padding: 2px 10px; font-weight: 700; font-size: 12px; text-transform: uppercase;">
                                                        Коригування
                                                    </span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="fw-bold" style="font-size: 16px; {{ $tx->points > 0 ? 'color: #34c759;' : 'color: #ff3b30;' }}">
                                                {{ $tx->points > 0 ? '+' : '' }}{{ $tx->points }}
                                            </span>
                                        </td>
                                        <td class="fw-bold">{{ number_format($tx->balance_after) }}</td>
                                        <td>{{ $tx->description }}</td>
                                        <td class="text-muted">{{ $tx->created_at?->format('d.m.Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @else
                        <div class="brutal-empty-state">
                            <div class="brutal-empty-state-icon">&#x1F381;</div>
                            <div class="brutal-empty-state-text">Транзакцій ще немає</div>
                            <p class="text-muted mt-2">Робіть покупки та отримуйте бали</p>
                        </div>
                    @endif
                </div>

                <!-- How it Works -->
                <div class="brutal-content-card">
                    <h2 class="brutal-subtitle">Як це працює</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div style="font-size: 48px; font-weight: 900; margin-bottom: 12px;">1</div>
                                <h4 class="fw-bold text-uppercase" style="font-size: 16px;">Купуйте</h4>
                                <p class="text-muted">Робіть покупки у нашому магазині та отримуйте бонусні бали за кожне замовлення</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div style="font-size: 48px; font-weight: 900; margin-bottom: 12px;">2</div>
                                <h4 class="fw-bold text-uppercase" style="font-size: 16px;">Накопичуйте</h4>
                                <p class="text-muted">Чим більше купуєте -- тим вищий рівень та більший множник балів</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div style="font-size: 48px; font-weight: 900; margin-bottom: 12px;">3</div>
                                <h4 class="fw-bold text-uppercase" style="font-size: 16px;">Використовуйте</h4>
                                <p class="text-muted">Обмінюйте бали на знижки. {{ $redemptionRate }} балів = 1 грн знижки</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
