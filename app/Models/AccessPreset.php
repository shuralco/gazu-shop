<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An access-rights preset (lightweight RBAC role). Bundles per-section
 * permissions in a JSON column. Assigned to users via users.access_preset_id.
 * is_admin users bypass presets entirely (super-admin).
 *
 * permissions shape: [SectionKey => ['view'=>bool,'create'=>bool,'update'=>bool,'delete'=>bool]]
 * SectionKey = Resource/Page class basename.
 */
class AccessPreset extends Model
{
    protected $fillable = ['key', 'name', 'description', 'permissions', 'is_system', 'sort_order'];

    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return array<string,array<string,bool>> */
    public function permissionMap(): array
    {
        return (array) ($this->permissions ?? []);
    }
}
