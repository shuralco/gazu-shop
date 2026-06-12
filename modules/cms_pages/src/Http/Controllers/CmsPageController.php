<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\Page;

/**
 * Фронтовий рендер CMS-сторінок (/page/{slug}) — модуль cms_pages.
 *
 * Раніше Page-записи не мали жодного роута (модель використовував лише блог),
 * тому /admin/pages був «мертвим». Тепер сторінка реально доступна на сайті,
 * а блоки зон layout.page.top / layout.page.bottom (модуль layout_builder)
 * прив'язуються до неї як модулі до layout в OpenCart.
 */
class CmsPageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::query()
            ->active()
            ->where(fn ($q) => $q->whereNull('template')->orWhere('template', '!=', 'blog_post'))
            ->where(function ($q) use ($slug) {
                $q->where('slug->uk', $slug)->orWhere('slug->en', $slug)->orWhere('slug', $slug);
            })
            ->first();

        if (! $page) {
            abort(404);
        }

        try {
            $page->increment('views');
        } catch (\Throwable) {
            // views — необов'язкова статистика, рендер не блокуємо
        }

        return view('gazu.page', [
            'page' => $page,
            'activeNav' => null,
        ]);
    }
}
