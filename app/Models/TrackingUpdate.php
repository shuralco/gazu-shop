<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status',
        'description',
        'location',
        'event_time',
        'raw_data',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'raw_data' => 'array',
    ];

    /**
     * Відправлення
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Отримати форматований опис події
     */
    public function getFormattedDescription(): string
    {
        $description = $this->description ?: $this->getStatusDescription();

        if ($this->location) {
            $description .= ' ('.$this->location.')';
        }

        return $description;
    }

    /**
     * Отримати опис статусу українською мовою
     */
    public function getStatusDescription(): string
    {
        return match ($this->status) {
            'pending' => 'Очікує обробки',
            'created' => 'Накладну створено',
            'picked_up' => 'Посилку забрано',
            'in_transit' => 'Посилка в дорозі',
            'out_for_delivery' => 'Посилка на доставці',
            'delivered' => 'Посилку доставлено',
            'failed' => 'Помилка доставки',
            'returned' => 'Посилка повернута',
            default => 'Невідомий статус',
        };
    }

    /**
     * Перевірити чи це останнє оновлення
     */
    public function isLatest(): bool
    {
        return $this->shipment->trackingUpdates()
            ->where('event_time', '>', $this->event_time)
            ->doesntExist();
    }

    /**
     * Отримати час в відносному форматі
     */
    public function getTimeAgo(): string
    {
        return $this->event_time->diffForHumans();
    }

    /**
     * Scope для статусу
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для часового діапазону
     */
    public function scopeBetweenDates(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('event_time', [$startDate, $endDate]);
    }

    /**
     * Scope для останніх оновлень
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('event_time', '>=', now()->subDays($days));
    }
}
