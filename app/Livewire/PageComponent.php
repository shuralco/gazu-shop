<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class PageComponent extends Component
{
    public Page $page;

    public function mount(string $slug): void
    {
        $page = Page::findBySlug($slug);

        if (!$page || !$page->is_active) {
            abort(404);
        }

        $this->page = $page;
    }

    public function render()
    {
        return view('livewire.page-component');
    }
}
