<div>
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Створити фільтр</h3>
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
                        <a wire:navigate href="{{ route('admin.filters') }}">
                            <div class="text-tiny">Фільтри</div>
                        </a>
                    </li>
                    <li>
                        <i class="icon-chevron-right"></i>
                    </li>
                    <li>
                        <div class="text-tiny">Створити</div>
                    </li>
                </ul>
            </div>

            <div class="wg-box">
                <form wire:submit.prevent="create">
                    <div class="form-group">
                        <label>Група фільтрів <span class="text-danger">*</span></label>
                        <select wire:model="filter_group_id" class="form-control">
                            <option value="">Оберіть групу</option>
                            @foreach($filterGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->title }}</option>
                            @endforeach
                        </select>
                        @error('filter_group_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Назва фільтра <span class="text-danger">*</span></label>
                        <input type="text" wire:model="title" class="form-control" 
                               placeholder="Наприклад: Червоний, XL, Бавовна">
                        @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Порядок сортування</label>
                        <input type="number" wire:model="sort_order" class="form-control" min="0">
                        @error('sort_order') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   wire:model="is_active" id="is_active">
                            <label class="form-check-label" for="is_active">
                                Активний
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="tf-button w208">
                            <i class="icon-save"></i> Зберегти
                        </button>
                        <a wire:navigate href="{{ route('admin.filters') }}" class="tf-button style-2 w208">
                            <i class="icon-x"></i> Скасувати
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>