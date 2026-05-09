<?php

namespace App\Livewire\Admin\Filter;

use App\Models\Filter;
use App\Models\FilterGroup;
use Livewire\Component;
use Livewire\WithPagination;

class FilterIndexComponent extends Component
{
    use WithPagination;

    public $search = '';

    public $filter_group_id = '';

    public function deleteFilter($id)
    {
        $filter = Filter::find($id);
        if ($filter) {
            $filter->delete();
            $this->js("toastr.success('Фільтр видалено')");
        }
    }

    public function render()
    {
        $filters = Filter::query()
            ->with('filterGroup')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%');
            })
            ->when($this->filter_group_id, function ($query) {
                $query->where('filter_group_id', $this->filter_group_id);
            })
            ->orderBy('filter_group_id')
            ->orderBy('sort_order')
            ->paginate(20);

        $filterGroups = FilterGroup::all();

        return view('livewire.admin.filter.filter-index-component', [
            'filters' => $filters,
            'filterGroups' => $filterGroups,
        ]);
    }
}
