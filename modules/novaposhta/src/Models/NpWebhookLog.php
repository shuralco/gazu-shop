<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpWebhookLog extends Model
{
    protected $table = 'np_webhook_logs';

    protected $fillable = [
        'ttn', 'status_code', 'status',
        'payload', 'signature_valid', 'processed',
        'ip', 'user_agent', 'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
    ];
}
