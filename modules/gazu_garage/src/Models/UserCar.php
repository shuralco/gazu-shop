<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'make', 'model', 'year', 'engine', 'body_type',
        'vin', 'plate', 'color', 'is_primary', 'meta',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'year' => 'integer',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Повертає рядок виду «Volkswagen Passat B8 2018». */
    public function getDisplayNameAttribute(): string
    {
        return trim(($this->make ?? '').' '.($this->model ?? '').' '.($this->year ?? ''));
    }

    /** Робить це авто primary, скидаючи прапорець з інших авто власника. */
    public function makePrimary(): void
    {
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->is_primary = true;
        $this->save();
    }
}
