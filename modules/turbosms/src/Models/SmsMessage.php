<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Журнал відправлених SMS/Viber (модуль turbosms). */
class SmsMessage extends Model
{
    protected $fillable = [
        'phone', 'template_key', 'channel', 'text',
        'message_id', 'status', 'error', 'order_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
