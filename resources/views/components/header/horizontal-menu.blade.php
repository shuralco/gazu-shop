@php
    use App\Models\DisplaySetting;
    use App\Services\HeaderService;
    
    $headerService = app(HeaderService::class);
    $config = $headerService->getHorizontalMenuConfig();
@endphp

{{-- Horizontal menu enabled --}}
@if($config['enabled'])
<div class="bg-black text-white relative hidden lg:block z-[100]" id="horizontalMenu">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        <div class="flex items-center h-12">
            @if($config['display_mode'] === 'horizontal_only' || $config['display_mode'] === 'both')
            <!-- Horizontal menu items -->
            <div class="flex items-center space-x-4 overflow-x-auto scrollbar-hide">
                @if(!empty($config['menu_items']))
                    @foreach($config['menu_items'] as $item)
                    @if(!empty(trim($item['text'])))
                    <div class="relative group flex-shrink-0">
                        <a wire:navigate href="{{ $item['url'] }}" class="text-white text-sm font-bold whitespace-nowrap hover:bg-white hover:text-black px-3 py-1 transition-colors">
                            {{ strtoupper(trim($item['text_' . app()->getLocale()] ?? $item['text'])) }}
                        </a>
                        
                        {{-- Show mega menu for categories if enabled --}}
                        @if($config['show_mega_menu'] && !empty($config['mega_menu_structure']['columns']))
                            @php
                                $categorySlug = str_replace('/', '', $item['url']);
                                $megaMenuCategory = null;
                                foreach($config['mega_menu_structure']['columns'] as $column) {
                                    foreach($column as $menuItem) {
                                        if($menuItem['type'] === 'category' && $menuItem['slug'] === $categorySlug) {
                                            $megaMenuCategory = $menuItem;
                                            break 2;
                                        }
                                    }
                                }
                            @endphp
                            
                            @if($megaMenuCategory)
                            <div class="horizontal-mega-menu group-hover:block">
                                <div class="bg-black text-white p-6 border-t border-gray-700">
                                    <div class="max-w-screen-2xl mx-auto">
                                        <div class="grid grid-cols-{{ min(count($megaMenuCategory['children']), 4) }} gap-6">
                                            @foreach(array_chunk($megaMenuCategory['children'], ceil(count($megaMenuCategory['children']) / min(count($megaMenuCategory['children']), 4))) as $chunk)
                                            <div>
                                                @foreach($chunk as $child)
                                                <a wire:navigate href="{{ locale_url($child['slug']) }}" class="horizontal-mega-menu-link">
                                                    {{ $child['title'] }}
                                                </a>
                                                @endforeach
                                            </div>
                                            @endforeach
                                        </div>
                                        
                                        @if($megaMenuCategory['show_all_link'])
                                        <div class="mt-4 pt-4 border-t border-gray-700">
                                            <a wire:navigate href="{{ locale_url($megaMenuCategory['slug']) }}" class="text-yellow-400 hover:text-yellow-300 font-medium text-sm">
                                                {{ __('general.view_all') }} →
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                    @endif
                    @endforeach
                @endif
            </div>
            @endif
        </div>
    </div>
    
</div>
@endif