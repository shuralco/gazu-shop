<?php

namespace App\Filament\Widgets;

use App\Models\ShippingApiLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Combined health overview for all shipping API providers (NP + UP) over 24h.
 * Replaces the legacy NP-only widget.
 */
class ShippingApiHealthWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return 'API доставки — здоровʼя за 24 години';
    }

    public static function canView(): bool
    {
        return \Schema::hasTable('shipping_api_logs');
    }

    protected function getStats(): array
    {
        $since = now()->subDay();

        $total = ShippingApiLog::where('created_at', '>=', $since)->count();
        $success = ShippingApiLog::where('created_at', '>=', $since)->where('success', true)->count();
        $errors = ShippingApiLog::where('created_at', '>=', $since)->where('success', false)->count();

        $successRate = $total > 0 ? round(($success / $total) * 100, 1) : null;
        $rateColor = match (true) {
            $successRate === null => 'gray',
            $successRate >= 95 => 'success',
            $successRate >= 80 => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Усього запитів', (string) $total)
                ->description($total > 0 ? "успіх: {$success} • помилок: {$errors}" : 'жодного запиту')
                ->color($total > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-arrows-right-left'),

            Stat::make('Успішність', $successRate !== null ? "{$successRate}%" : '—')
                ->description($total === 0 ? '—' : "{$success}/{$total} OK")
                ->color($rateColor)
                ->icon($rateColor === 'success' ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-triangle'),

            $this->providerStat('novaposhta', 'Нова Пошта', 'info'),
            $this->providerStat('ukrposhta', 'УкрПошта', 'warning'),
        ];
    }

    private function providerStat(string $provider, string $label, string $okColor): Stat
    {
        $since = now()->subDay();

        $errors = ShippingApiLog::forProvider($provider)
            ->where('created_at', '>=', $since)
            ->where('success', false)
            ->count();

        $hourly = [];
        for ($h = 23; $h >= 0; $h--) {
            $start = now()->subHours($h)->startOfHour();
            $end = $start->copy()->endOfHour();
            $hourly[] = ShippingApiLog::forProvider($provider)
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->where('success', false)
                ->count();
        }

        return Stat::make($label, "{$errors} помилок")
            ->description('за 24 год')
            ->color($errors > 0 ? 'danger' : $okColor)
            ->chart($hourly)
            ->icon('heroicon-o-bug-ant');
    }
}
