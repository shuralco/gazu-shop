<?php

namespace App\Livewire\Admin\FilterGroup;

use App\Models\Category;
use App\Models\FilterGroup;
use Livewire\Component;

class FilterGroupCreateComponent extends Component
{
    public $title = '';

    public $is_active = true;

    public $sort_order = 0;

    public $selected_categories = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    public function create()
    {
        $this->validate();

        $filterGroup = FilterGroup::create([
            'title' => $this->title,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ]);

        // Attach to categories
        if (! empty($this->selected_categories)) {
            $filterGroup->categories()->attach($this->selected_categories);
        }

        $this->js("toastr.success('Групу фільтрів створено')");

        return redirect()->route('admin.filter-groups');
    }

    public function render()
    {
        $categories = Category::all();

        return view('livewire.admin.filter-group.filter-group-create-component', [
            'categories' => $categories,
        ]);
    }
}
