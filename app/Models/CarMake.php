<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarMake extends Model
{
    protected $fillable = [
        'slug', 'name', 'logo_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(CarModel::class, 'make_id');
    }

    /**
     * Resolve a usable logo URL from logo_path regardless of how it was stored:
     *   - full URL (http…)                              → as-is
     *   - root-relative public path ("/img/…")          → url()
     *   - Filament upload (public disk, "car-makes/x")   → /storage/car-makes/x
     * Returns null when no logo set (caller falls back to a letter badge).
     */
    public function getLogoUrlAttribute(): ?string
    {
        $p = $this->logo_path;
        if (! $p) {
            return null;
        }
        if (\Illuminate\Support\Str::startsWith($p, ['http://', 'https://'])) {
            return $p;
        }
        if (\Illuminate\Support\Str::startsWith($p, '/')) {
            return url($p);
        }
        return asset('storage/'.ltrim($p, '/'));
    }
}
