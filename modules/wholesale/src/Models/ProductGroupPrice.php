<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductGroupPrice extends Model
{
    protected $fillable = [
        'product_id',
        'customer_group_id',
        'price',
        'price_currency',
        'min_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_quantity' => 'integer',
    ];

    /** Гуртова ціна у грн (конверсія за курсом /admin/currencies). */
    public function getDisplayPriceAttribute(): float
    {
        return \App\Models\Currency::toBase($this->price, $this->price_currency);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
