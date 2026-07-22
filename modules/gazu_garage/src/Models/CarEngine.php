<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CarEngine extends Model
{
    protected $fillable = [
        'model_id', 'code', 'label', 'fuel_type', 'displacement', 'hp',
        'years_range', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'model_id' => 'integer',
        'hp' => 'integer',
        'displacement' => 'decimal:1',
    ];

    /**
     * URL-безпечний slug коду двигуна для pretty-URL /zapchastyny/{make}/{model}/{engine}.
     *
     * Коди двигунів містять пробіли («RWD 100 kWh 2021-»), подвійні пробіли й
     * навіть слеш («007 / 7GT») — сирий code у шляху дає 404 (route-констрейнт
     * приймає лише [A-Za-z0-9.\-]). Тому в URL іде slug, а бекенд резолвить його
     * назад у реальний code. JS-двійник у car-selector.blade.php мусить бути
     * ІДЕНТИЧНИМ: c.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'').
     */
    public static function urlSlug(?string $code): string
    {
        $s = mb_strtolower(trim((string) $code));
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s);

        return trim((string) $s, '-');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'model_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_compatibility', 'engine_id', 'product_id')
            ->withPivot('note')
            ->withTimestamps();
    }
}
