<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchEditorLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_type',
        'description',
        'filter_params',
        'affected_ids',
        'changes_data',
        'affected_count',
        'rolled_back',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'filter_params' => 'array',
            'affected_ids' => 'array',
            'changes_data' => 'array',
            'rolled_back' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
