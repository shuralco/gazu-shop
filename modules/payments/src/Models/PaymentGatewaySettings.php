<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'fee_percentage' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
    ];
}
