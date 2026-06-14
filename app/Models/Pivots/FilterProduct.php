<?php

namespace App\Models\Pivots;

use App\Models\Filter;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot товар↔характеристика (filter_products).
 *
 * Faceted-фільтр у каталозі групує умови за filter_group_id
 * (havingRaw count(distinct filter_group_id)), тож цей стовпець ОБОВ'ЯЗКОВО
 * має бути заповнений. При призначенні характеристики у формі Filament
 * передається лише filter_id — group_id підставляємо автоматично з самого
 * фільтра, щоб клієнту не доводилось дублювати групу вручну.
 */
class FilterProduct extends Pivot
{
    protected $table = 'filter_products';

    public $incrementing = false;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::saving(function (self $pivot): void {
            if (empty($pivot->filter_group_id) && ! empty($pivot->filter_id)) {
                $pivot->filter_group_id = Filter::whereKey($pivot->filter_id)->value('filter_group_id');
            }
        });
    }
}
