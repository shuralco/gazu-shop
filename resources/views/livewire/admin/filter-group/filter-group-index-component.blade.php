<div>
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Групи фільтрів</h3>
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
                        <div class="text-tiny">Групи фільтрів</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <div class="flex items-center justify-between gap10 flex-wrap">
                    <div class="wg-filter flex-grow">
                        <input type="text" placeholder="Пошук груп..." 
                               wire:model.live="search" class="form-control">
                    </div>
                    <a wire:navigate href="{{ route('admin.filter-group.create') }}" class="tf-button style-1 w208">
                        <i class="icon-plus"></i>Додати групу
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Назва</th>
                                <th>К-сть фільтрів</th>
                                <th>Активна</th>
                                <th>Порядок</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filterGroups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->title }}</td>
                                    <td>{{ $group->filters_count }}</td>
                                    <td>
                                        @if($group->is_active)
                                            <span class="badge bg-success">Так</span>
                                        @else
                                            <span class="badge bg-danger">Ні</span>
                                        @endif
                                    </td>
                                    <td>{{ $group->sort_order ?? 0 }}</td>
                                    <td>
                                        <div class="list-icon-function">
                                            <a wire:navigate href="{{ route('admin.filter-group.edit', $group->id) }}">
                                                <div class="item edit">
                                                    <i class="icon-edit-3"></i>
                                                </div>
                                            </a>
                                            <a href="#" wire:click.prevent="deleteFilterGroup({{ $group->id }})"
                                               onclick="return confirm('Видалити групу фільтрів?')">
                                                <div class="item text-danger delete">
                                                    <i class="icon-trash-2"></i>
                                                </div>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Групи фільтрів не знайдено</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="divider"></div>
                <div class="flex items-center justify-between flex-wrap gap10 wgp-pagination">
                    {{ $filterGroups->links('components.admin.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>