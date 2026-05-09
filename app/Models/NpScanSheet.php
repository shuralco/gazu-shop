<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NpScanSheet extends Model
{
    protected $table = 'np_scan_sheets';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PRINTED = 'printed';
    public const STATUS_HANDED_OVER = 'handed_over';

    protected $fillable = [
        'ref', 'number', 'date',
        'shipments_count', 'total_weight', 'total_cost',
        'status', 'printed_at', 'print_meta',
    ];

    protected $casts = [
        'date' => 'date',
        'printed_at' => 'datetime',
        'shipments_count' => 'integer',
        'total_weight' => 'decimal:3',
        'total_cost' => 'decimal:2',
        'print_meta' => 'array',
    ];

    public function shipments(): HasMany
    {
        return $this->hasMany(NpShipment::class, 'registry_ref', 'ref');
    }

    public function recalculateTotals(): void
    {
        $totals = $this->shipments()
            ->selectRaw('COUNT(*) as cnt, SUM(weight) as w, SUM(cost) as c')
            ->first();

        $this->update([
            'shipments_count' => (int) $totals->cnt,
            'total_weight' => (float) ($totals->w ?? 0),
            'total_cost' => (float) ($totals->c ?? 0),
        ]);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('date', $date);
    }
}
