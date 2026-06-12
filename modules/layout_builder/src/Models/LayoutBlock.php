<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * LayoutBlock — призначення блоку у зону storefront (layout_builder module).
 *
 * Рендериться через Hooks::on() у LayoutBuilderServiceProvider::boot()
 * для відповідної зони. Admin керує через App\Filament\Pages\LayoutBuilderPage.
 */
class LayoutBlock extends Model
{
    protected $table = 'layout_blocks';

    protected $fillable = [
        'zone',
        'type',
        'title',
        'content',
        'config',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Доступні зони storefront (значення = ключ hook-point без префіксу layout.). */
    public const ZONES = [
        'home.top' => 'Головна — верх (layout.home.top)',
        'home.bottom' => 'Головна — низ (layout.home.bottom)',
        'product.sidebar' => 'Картка товару — сайдбар (layout.product.sidebar)',
        'page.top' => 'CMS-сторінка — верх (layout.page.top)',
        'page.bottom' => 'CMS-сторінка — низ (layout.page.bottom)',
    ];

    /** Типи блоків. */
    public const TYPES = [
        'html' => 'HTML / текст',
        'banner' => 'Банер (зображення + посилання)',
        'featured' => 'Рекомендовані товари',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeForZone(Builder $q, string $zone): Builder
    {
        return $q->where('zone', $zone);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    /** Активні блоки зони у порядку sort_order ASC. */
    public static function renderable(string $zone): \Illuminate\Support\Collection
    {
        return static::query()->active()->forZone($zone)->ordered()->get();
    }
}
