<div>
    <div class="main-content-inner">
        <div class="main-content-wrap">
            <div class="flex items-center flex-wrap justify-between gap20 mb-27">
                <h3>Створити групу фільтрів</h3>
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
                        <a wire:navigate href="{{ route('admin.filter-groups') }}">
                            <div class="text-tiny">Групи фільтрів</div>
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
                        <label>Назва групи <span class="text-danger">*</span></label>
                        <input type="text" wire:model="title" class="form-control" 
                               placeholder="Наприклад: Колір, Розмір, Матеріал">
                        @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Порядок сортування</label>
                        <input type="number" wire:model="sort_order" class="form-control" min="0">
                        @error('sort_order') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label>Категорії</label>
                        <div class="checkbox-list">
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="selected_categories" 
                                           value="{{ $category->id }}"
                                           id="cat_{{ $category->id }}">
                                    <label class="form-check-label" for="cat_{{ $category->id }}">
                                        {{ $category->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   wire:model="is_active" id="is_active">
                            <label class="form-check-label" for="is_active">
                                Активна
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="tf-button w208">
                            <i class="icon-save"></i> Зберегти
                        </button>
                        <a wire:navigate href="{{ route('admin.filter-groups') }}" class="tf-button style-2 w208">
                            <i class="icon-x"></i> Скасувати
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>