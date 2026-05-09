<?php

namespace App\Livewire\Admin\FilterGroup;

use App\Models\FilterGroup;
use Livewire\Component;
use Livewire\WithPagination;

class FilterGroupIndexComponent extends Component
{
    use WithPagination;

    public $search = '';

    public function deleteFilterGroup($id)
    {
        $filterGroup = FilterGroup::find($id);
        if ($filterGroup) {
            $filterGroup->delete();
            $this->js("toastr.success('Групу фільтрів видалено')");
        }
    }

    public function render()
    {
        $filterGroups = FilterGroup::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%');
            })
            ->withCount('filters')
            ->paginate(15);

        return view('livewire.admin.filter-group.filter-group-index-component', [
            'filterGroups' => $filterGroups,
        ]);
    }
}
