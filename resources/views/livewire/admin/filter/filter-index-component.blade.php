<div>
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Фільтри</h3>
                <ul class="breadcrumbs flex items-center flex-wrap justify-start gap10">
                    <li>
                        <a wire:navigate href="{{ route('admin.index') }}">
                            <div class="text-tiny">Dashboard</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Фільтри</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <div class="flex gap-2">
                            <input type="text" placeholder="Пошук фільтрів..." 
                                   wire:model.live="search" class="form-control">
                            <select wire:model.live="filter_group_id" class="form-control">
                                <option value="">Всі групи</option>
                                @foreach($filterGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <a wire:navigate href="{{ route('admin.filter.create') }}" class="tf-button style-1 w208">
                        <i class="icon-plus"></i>Додати фільтр
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Група</th>
                                <th>Назва</th>
                                <th>Активний</th>
                                <th>Порядок</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filters as $filter)
                                <tr>
                                    <td>{{ $filter->id }}</td>
                                    <td>{{ $filter->filterGroup->title ?? 'Без групи' }}</td>
                                    <td>{{ $filter->title }}</td>
                                    <td>
                                        @if($filter->is_active)
                                            <span class="badge bg-success">Так</span>
                                        @else
                                            <span class="badge bg-danger">Ні</span>
                                        @endif
                                    </td>
                                    <td>{{ $filter->sort_order ?? 0 }}</td>
                                    <td>
                                        <div class="list-icon-function">
                                            <a wire:navigate href="{{ route('admin.filter.edit', $filter->id) }}">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                            <a href="#" wire:click.prevent="deleteFilter({{ $filter->id }})"
                                               onclick="return confirm('Видалити фільтр?')">
                                                <div class="item text-danger delete">
                                                    <i class="icon-trash-2"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Фільтри не знайдено</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $filters->links('components.admin.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>