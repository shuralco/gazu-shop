{{-- Reusable product breadcrumb trail: Home → Catalog → ...category chain → current.
     Expects $p (product), $brand, $oem, $name (string) in scope.
     If $skipHome is true (e.g. compact v3 layout) — Home crumb is omitted. --}}
@php
    $skipHome = $skipHome ?? false;
    $crumbs = $skipHome ? [] : [['Головна', route('gazu.home')]];
    $crumbs[] = ['Каталог', route('gazu.catalog')];
    if (is_object($p) && ($cat = $p->category ?? null)) {
        $chain = [];
        $seen = [];
        while ($cat && !isset($seen[$cat->id])) {
            $seen[$cat->id] = true;
            array_unshift($chain, $cat);
            $cat = $cat->parent ?? null;
        }
        foreach ($chain as $c) {
            $title = method_exists($c, 'getTranslation')
                ? ($c->getTranslation('title', app()->getLocale(), false)
                    ?: ($c->getTranslation('title', 'uk', false) ?: ($c->name ?? '')))
                : ($c->title ?? $c->name ?? '');
            $slug = method_exists($c, 'getLocalizedSlug') ? $c->getLocalizedSlug() : ($c->slug ?? $c->id);
            $crumbs[] = [(string) $title, url('/'.($slug ?: $c->id))];
        }
    }
    $crumbs[] = trim(($brand ?? '') . ' ' . ($oem ?? '')) ?: ($name ?? 'Товар');
@endphp
<x-gazu.breadcrumbs :items="$crumbs"/>
