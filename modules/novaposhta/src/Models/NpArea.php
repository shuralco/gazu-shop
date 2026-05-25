<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NpArea extends Model
{
    protected $table = 'np_areas';

    protected $fillable = [
        'ref',
        'description',
    ];

    /**
     * Міста цієї області
     */
    public function cities(): HasMany
    {
        return $this->hasMany(NpCity::class, 'area_ref', 'ref');
    }
}
