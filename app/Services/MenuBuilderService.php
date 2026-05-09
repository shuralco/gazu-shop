<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DisplaySetting;

class MenuBuilderService
{
    public function __construct(private Category $category) {}

    public function buildTopBar(array $config): string
    {
        if (! $config['show']) {
            return '';
        }

        $html = '<div class="bg-gray-100 border-b-2 border-black">';
        $html .= '<div class="max-w-screen-2xl mx-auto px-4 md:px-8">';
        $html .= '<div class="flex justify-between items-center h-12 text-sm">';

        // Left side - contacts
        $html .= '<div class="flex items-center space-x-6">';

        // Phone
        $html .= '<div class="flex items-center space-x-2">';
        $html .= '<span>📞</span>';
        $html .= '<a href="tel:'.$config['phone'].'" class="font-medium text-black hover:underline">'.$config['phone'].'</a>';
        $html .= '</div>';

        // Email (desktop only)
        $html .= '<div class="hidden md:flex items-center space-x-2">';
        $html .= '<span>📧</span>';
        $html .= '<a href="mailto:'.$config['email'].'" class="font-medium text-black hover:underline">'.$config['email'].'</a>';
        $html .= '</div>';

        // Working hours (large screens only)
        $html .= '<div class="hidden lg:block">';
        $html .= '<span class="text-xs font-bold">'.$config['working_hours'].'</span>';
        $html .= '</div>';

        $html .= '</div>';

        // Right side - social and promo
        $html .= '<div class="flex items-center space-x-4">';

        // Social links
        if ($config['show_social']) {
            $html .= '<div class="hidden md:flex space-x-2">';

            if ($config['facebook_url'] && $config['facebook_url'] !== '#') {
                $html .= '<a href="'.$config['facebook_url'].'" class="w-6 h-6 border border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors text-xs">';
                $html .= '<i class="fa-brands fa-facebook-f"></i>';
                $html .= '</a>';
            }

            if ($config['instagram_url'] && $config['instagram_url'] !== '#') {
                $html .= '<a href="'.$config['instagram_url'].'" class="w-6 h-6 border border-black flex items-center justify-center hover:bg-black hover:text-white transition-colors text-xs">';
                $html .= '<i class="fa-brands fa-instagram"></i>';
                $html .= '</a>';
            }

            $html .= '</div>';
        }

        // Promo text
        $html .= '<div class="text-xs font-bold">'.$config['promo_text'].'</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function buildMainHeader(array $config): string
    {
        $html = '<div class="bg-white">';
        $html .= '<div class="max-w-screen-2xl mx-auto px-4 md:px-8">';
        $html .= '<div class="flex justify-between items-center h-16 md:h-20">';

        // Logo section
        $html .= '<div class="flex items-center">';
        $html .= '<a wire:navigate href="'.locale_route('home').'" class="text-2xl md:text-4xl font-black text-black tracking-tight">';
        $html .= $config['logo_text'];
        $html .= '</a>';
        $html .= '</div>';

        // Desktop menu
        $html .= '<div class="hidden lg:flex items-center space-x-6 desktop-menu">';

        // Catalog button with mega menu
        $html .= '<button id="catalogBtn" class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors flex items-center">';
        $html .= $config['menu_catalog_text'];
        $html .= '<svg class="w-4 h-4 ml-1 transition-transform" id="catalogArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>';
        $html .= '</svg>';
        $html .= '</button>';

        // Other menu items
        $html .= '<button class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">'.$config['menu_brands_text'].'</button>';
        $html .= '<a wire:navigate href="'.route('specials').'" class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">'.$config['menu_specials_text'].'</a>';
        $html .= '<button class="text-black text-base font-bold hover:bg-black hover:text-white px-3 py-2 transition-colors">'.$config['menu_help_text'].'</button>';

        $html .= '</div>';

        // Right side - search, user, cart, mobile menu
        $html .= '<div class="flex items-center space-x-3">';
        $html .= '<div class="relative hidden md:block">';
        $html .= '<livewire:search.search-form-component />';
        $html .= '</div>';
        $html .= '<div class="hidden md:block">';
        $html .= '<livewire:user.nav-component />';
        $html .= '</div>';
        $html .= '<div class="relative">';
        $html .= '<livewire:cart.cart-icon-component />';
        $html .= '<livewire:cart.cart-modal-component />';
        $html .= '</div>';

        // Mobile menu button
        $html .= '<button id="openMobileMenu" class="lg:hidden">';
        $html .= '<svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>';
        $html .= '</svg>';
        $html .= '</button>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function buildMegaMenu(array $config): string
    {
        if (! $config['enabled']) {
            return '';
        }

        $html = '<div class="mega-menu" id="megaMenu">';
        $html .= '<div class="max-w-screen-2xl mx-auto p-8 md:p-12">';

        $gridCols = $config['columns'] + ($config['show_promo'] ? 1 : 0);
        $html .= '<div class="grid grid-cols-2 md:grid-cols-'.$gridCols.' gap-8 md:gap-12">';

        // Categories columns
        foreach ($config['categories'] as $category) {
            $html .= '<div>';
            $html .= '<h4 class="font-black text-black mb-6 text-xl border-b-4 border-black pb-3">'.strtoupper($category->title).'</h4>';
            $html .= '<ul class="space-y-3">';

            if ($category->children->isNotEmpty()) {
                foreach ($category->children as $child) {
                    $html .= '<li><a wire:navigate href="/'.$child->slug.'" class="text-black text-base font-medium hover:font-black transition-all">'.$child->title.'</a></li>';
                }

                if ($category->children->count() >= $config['subcategories_limit']) {
                    $html .= '<li><a wire:navigate href="/'.$category->slug.'" class="text-black text-sm font-black hover:underline">+ ВСЕ В КАТЕГОРІЇ</a></li>';
                }
            }

            $html .= '</ul>';
            $html .= '</div>';
        }

        // Promo block
        if ($config['show_promo']) {
            $html .= '<div class="bg-black text-white p-6">';
            $html .= '<h4 class="font-black text-white mb-4 text-xl">'.$config['promo_title'].'</h4>';
            $html .= '<p class="text-white mb-6 text-base">'.$config['promo_subtitle'].'</p>';
            $html .= '<button class="btn-white w-full mb-4">'.$config['promo_button'].'</button>';
            $html .= '<div class="border-t-2 border-white pt-4 mt-4">';
            $html .= '<p class="text-sm text-gray-300 mb-2">'.$config['phone_label'].'</p>';
            $html .= '<p class="text-xl font-black text-white">'.shopPhone().'</p>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function buildHorizontalMenu(array $config): string
    {
        $mode = $config['mode'];

        if ($mode === 'catalog_only') {
            return $this->buildCatalogOnlyMenu();
        }

        if (! in_array($mode, ['horizontal_only', 'both'])) {
            return '';
        }

        $style = $config['style'] ?? 'buttons';
        $background = $config['background'] ?? '#000000';

        $html = '<div class="hidden md:block" style="background-color: '.$background.';">';
        $html .= '<div class="max-w-screen-2xl mx-auto px-4 md:px-8">';

        if ($style === 'tabs') {
            $html .= $this->buildTabsStyle($config);
        } elseif ($style === 'links') {
            $html .= $this->buildLinksStyle($config);
        } else {
            $html .= $this->buildButtonsStyle($config);
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    private function buildCatalogOnlyMenu(): string
    {
        return '<div class="bg-black text-white hidden md:block">
                    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
                        <div class="flex items-center justify-center h-12">
                            <button id="horizontalCatalogBtn" class="text-white font-bold hover:bg-white hover:text-black px-6 py-2 transition-colors">
                                '.DisplaySetting::get('menu_catalog_text', 'КАТАЛОГ').'
                            </button>
                        </div>
                    </div>
                </div>';
    }

    private function buildButtonsStyle(array $config): string
    {
        $html = '<div class="flex items-center justify-center space-x-8 h-12">';

        foreach ($config['categories'] as $category) {
            $html .= '<a wire:navigate href="/'.$category->slug.'" class="text-white text-sm font-bold hover:bg-white hover:text-black px-4 py-2 transition-colors">';
            $html .= strtoupper($category->title);
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    private function buildTabsStyle(array $config): string
    {
        $html = '<div class="flex items-center justify-center h-12 border-b-2 border-white">';

        foreach ($config['categories'] as $category) {
            $html .= '<button class="text-white text-sm font-bold hover:bg-white hover:text-black px-6 py-3 border-r border-gray-600 last:border-r-0 transition-colors tab-item">';
            $html .= strtoupper($category->title);
            $html .= '</button>';
        }

        $html .= '</div>';

        return $html;
    }

    private function buildLinksStyle(array $config): string
    {
        $html = '<div class="flex items-center justify-center space-x-12 h-12">';

        foreach ($config['categories'] as $category) {
            $html .= '<a wire:navigate href="/'.$category->slug.'" class="text-white text-sm font-medium hover:font-bold hover:underline transition-all">';
            $html .= strtoupper($category->title);
            $html .= '</a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function buildHorizontalMegaMenu(array $config): string
    {
        if ($config['mode'] !== 'horizontal_only' && $config['mode'] !== 'both') {
            return '';
        }

        $html = '<div class="horizontal-mega-menu" id="horizontalMegaMenu" style="display: none;">';
        $html .= '<div class="bg-white border-b-4 border-black p-8">';
        $html .= '<div class="max-w-screen-2xl mx-auto">';
        $html .= '<div class="grid grid-cols-2 md:grid-cols-4 gap-8">';

        // Get all categories for horizontal mega menu
        $categories = $this->category
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('sort_order')->limit(8);
            }])
            ->orderBy('sort_order')
            ->get();

        foreach ($categories->take(4) as $category) {
            $html .= '<div>';
            $html .= '<h4 class="font-black text-black mb-4 text-lg border-b-2 border-black pb-2">'.strtoupper($category->title).'</h4>';
            $html .= '<ul class="space-y-2">';

            foreach ($category->children as $child) {
                $html .= '<li><a wire:navigate href="/'.$child->slug.'" class="text-black text-sm font-medium hover:font-black transition-all">'.$child->title.'</a></li>';
            }

            $html .= '</ul>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
