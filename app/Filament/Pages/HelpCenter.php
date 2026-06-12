<?php

namespace App\Filament\Pages;

use App\Models\HelpArticle;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

/**
 * «Інструкції» — читацька вітрина довідки/wiki адмінки. Сайдбар тем (статті
 * з БД, згруповані по розділах) + рендер Markdown обраної статті. Контекстна
 * кнопка «Довідка» на ресурсах веде сюди з ?topic={slug}. Редагування —
 * App\Filament\Resources\HelpArticleResource.
 */
class HelpCenter extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Обслуговування';

    protected static ?string $navigationLabel = 'Інструкції';

    protected static ?string $title = 'Інструкції / Довідка';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'help';

    protected static string $view = 'filament.pages.help-center';

    public ?string $topic = null;

    public function mount(): void
    {
        $this->topic = request()->query('topic');
    }

    /** Активні статті, згруповані по розділу (section), відсортовані. */
    public function getGroupedArticles(): Collection
    {
        return HelpArticle::query()
            ->active()
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->groupBy('section');
    }

    public function getCurrentArticle(): ?HelpArticle
    {
        $q = HelpArticle::query()->active();

        if ($this->topic) {
            $byTopic = (clone $q)->where('slug', $this->topic)->first();
            if ($byTopic) {
                return $byTopic;
            }
        }

        // дефолт: pinned overview або перша стаття
        return (clone $q)->orderByRaw("CASE WHEN slug = 'overview' THEN 0 ELSE 1 END")
            ->orderBy('section')->orderBy('sort_order')->first();
    }
}
