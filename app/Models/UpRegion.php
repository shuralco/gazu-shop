<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpRegion extends Model
{
    protected $table = 'up_regions';

    public $incrementing = false;

    protected $fillable = ['id', 'name_ua', 'name_en'];

    protected function casts(): array
    {
        return ['id' => 'integer'];
    }
}
