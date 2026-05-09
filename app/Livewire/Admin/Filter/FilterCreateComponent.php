<?php

namespace App\Livewire\Admin\Filter;

use App\Models\Filter;
use App\Models\FilterGroup;
use Livewire\Component;

class FilterCreateComponent extends Component
{
    public $title = '';

    public $filter_group_id = '';

    public $is_active = true;

    public $sort_order = 0;

    protected $rules = [
        'title' => 'required|string|max:255',
        'filter_group_id' => 'required|exists:filter_groups,id',
        'is_active' => 'boolean',
        'sort_order' => 'integer|min:0',
    ];

    public function create()
    {
        $this->validate();

        Filter::create([
            'title' => $this->title,
            'filter_group_id' => $this->filter_group_id,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ]);

        $this->js("toastr.success('Фільтр створено')");

        return redirect()->route('admin.filters');
    }

    public function render()
    {
        $filterGroups = FilterGroup::all();

        return view('livewire.admin.filter.filter-create-component', [
            'filterGroups' => $filterGroups,
        ]);
    }
}
