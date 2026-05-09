<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'old_price',
        'quantity',
        'stock_status',
        'image',
        'option_values',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'option_values' => 'array',
        'is_active' => 'boolean',
        'quantity' => 'integer',
        'weight' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductOptionValue::class, 'variant_option_values');
    }

    public function getEffectivePrice(): float
    {
        if ($this->price !== null) {
            return (float) $this->price;
        }

        $basePrice = (float) $this->product->price;
        $modifier = $this->optionValues->sum('price_modifier');

        return $basePrice + $modifier;
    }

    public function getDisplayName(): string
    {
        if (!$this->option_values || !is_array($this->option_values)) {
            return '';
        }

        return implode(' / ', array_values($this->option_values));
    }

    public function isInStock(): bool
    {
        return $this->stock_status === 'in_stock' && $this->quantity > 0;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
