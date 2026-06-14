<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PaymentGatewaySettings extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'configuration',
        'fee_percentage',
        'min_amount',
        'max_amount',
        'currency',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'fee_percentage' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /** Активні способи оплати у порядку sort_order — для checkout. */
    public function scopeActiveOrdered(Builder $q): Builder
    {
        $q->where('is_active', true);
        if (Schema::hasColumn('payment_gateway_settings', 'sort_order')) {
            $q->orderBy('sort_order');
        }

        return $q->orderBy('id');
    }
}
