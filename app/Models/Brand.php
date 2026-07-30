<?php

namespace App\Models;

use App\Traits\HasSeoMeta;
use App\Traits\TranslatableToArray;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    use HasSeoMeta;
    use HasTranslations;
    use Sluggable;
    use TranslatableToArray;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'sort_order',
    ];

    /**
     * Лого бренду для вітрини.
     *
     * Порядок: власне завантажене лого → лого марки авто з такою ж назвою →
     * офіційний файл із репозиторію → null (тоді шаблон показує назву).
     *
     * Навіщо марка: клієнт заливає емблеми в «Марки авто», а бренди — окрема
     * сутність із тими самими назвами («VW», «VW-FAW», «BYD Aftermarket»).
     * Без цього мосту плитки брендів лишалися порожніми, хоча файл уже залитий.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($own = \App\Support\UploadedImage::url($this->logo ?: null)) {
            return $own;
        }

        $token = self::logoToken((string) $this->name);
        if ($token === '') {
            return null;
        }

        $makes = \Illuminate\Support\Facades\Cache::remember(
            'brand_logo:makes',
            3600,
            fn () => class_exists(\App\Models\CarMake::class)
                ? \App\Models\CarMake::query()->get(['name', 'slug', 'logo_path'])
                    ->mapWithKeys(fn ($m) => [self::logoToken((string) $m->name) => $m->logo_url])
                    ->filter()->all()
                : []
        );

        if (! empty($makes[$token])) {
            return $makes[$token];
        }

        foreach (['svg', 'png'] as $ext) {
            if (is_file(public_path("img/car-makes/{$token}.{$ext}"))) {
                return asset("img/car-makes/{$token}.{$ext}");
            }
        }

        return null;
    }

    /**
     * Назва → ключ для пошуку емблеми: перше слово, латиниця/цифри, аліаси.
     * «VW-FAW» → vw, «BYD Aftermarket» → byd, «Volkswagen» → vw.
     */
    public static function logoToken(string $name): string
    {
        $first = preg_split('/[\s\-_\/]+/u', mb_strtolower(trim($name)))[0] ?? '';
        $first = preg_replace('/[^a-z0-9]/', '', $first) ?? '';

        return ['volkswagen' => 'vw'][$first] ?? $first;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function filters(): BelongsToMany
    {
        return $this->belongsToMany(Filter::class, 'brand_filters');
    }
}
