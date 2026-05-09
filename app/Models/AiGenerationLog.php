<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'provider',
        'model',
        'prompt',
        'response',
        'tokens_used',
        'products_created',
        'products_updated',
        'errors',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'tokens_used' => 'integer',
            'products_created' => 'integer',
            'products_updated' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'success',
            'error' => 'danger',
            'pending' => 'warning',
            default => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'products' => 'Генерація товарів',
            'enrichment' => 'Збагачення',
            'seo' => 'SEO мета',
            'translation' => 'Переклад',
            'tags' => 'Пошукові теги',
            default => $this->type,
        };
    }
}
