<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Persistent module state. Owns the `enabled` flag plus per-module settings
 * overrides. Source of truth lookup chain (DB → ENV → config) is implemented
 * in App\Support\ModuleManager, not here — this is just the storage layer.
 */
class Module extends Model
{
    protected $fillable = [
        'key', 'enabled', 'settings', 'installed_version', 'enabled_at', 'disabled_at',
    ];

    protected $casts = [
        'enabled' => 'bool',
        'settings' => 'array',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];
}
