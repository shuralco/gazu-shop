<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['module_key', 'action', 'payload', 'user_id', 'ip', 'created_at'];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];
}
