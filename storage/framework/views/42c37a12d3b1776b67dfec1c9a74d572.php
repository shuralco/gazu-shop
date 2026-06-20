<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'products' => null,
    'paginationInfo' => []
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'products' => null,
    'paginationInfo' => []
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="hybrid-pagination-container">
    <?php if($products && method_exists($products, 'hasPages')): ?>
        
        <!-- Desktop: Traditional Pagination + Load More in one line -->
        <div class="hidden md:flex justify-between items-center gap-6 bg-white border-4 border-black p-4 mb-8">
            
            <!-- Traditional Pagination (Left Side) -->
            <div class="flex items-center gap-2">
                <?php if($products->onFirstPage()): ?>
                    <span class="pagination-btn border-2 border-gray-300 px-4 py-2 text-gray-400 cursor-not-allowed font-bold">
                        ← <?php echo e(__('general.prev')); ?>

                    </span>
                <?php else: ?>
                    <button wire:click="previousPage" 
                            wire:loading.attr="disabled"
                            class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                        ← <?php echo e(__('general.prev')); ?>

                    </button>
                <?php endif; ?>
                
                <?php $__currentLoopData = $products->getUrlRange(1, min(5, $products->lastPage())); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $products->currentPage()): ?>
                        <span class="pagination-btn bg-black text-white border-2 border-black px-4 py-2 font-bold">
                            <?php echo e($page); ?>

                        </span>
                    <?php else: ?>
                        <button wire:click="gotoPage(<?php echo e($page); ?>)" 
                                wire:loading.attr="disabled"
                                class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                            <?php echo e($page); ?>

                        </button>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
                <?php if($products->lastPage() > 5): ?>
                    <span class="px-2 font-bold">...</span>
                    <button wire:click="gotoPage(<?php echo e($products->lastPage()); ?>)" 
                            wire:loading.attr="disabled"
                            class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                        <?php echo e($products->lastPage()); ?>

                    </button>
                <?php endif; ?>
                
                <?php if($products->hasMorePages()): ?>
                    <button wire:click="nextPage" 
                            wire:loading.attr="disabled"
                            class="pagination-btn border-2 border-black px-4 py-2 hover:bg-black hover:text-white font-bold transition-all">
                        <?php echo e(__('general.next')); ?> →
                    </button>
                <?php else: ?>
                    <span class="pagination-btn border-2 border-gray-300 px-4 py-2 text-gray-400 cursor-not-allowed font-bold">
                        <?php echo e(__('general.next')); ?> →
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Load More Section (Right Side) -->
            <div class="flex items-center gap-4">
                <!-- Progress Info -->
                <div class="text-sm font-bold text-gray-600">
                    <?php echo e(__('general.showing_of', ['shown' => $paginationInfo['currentItemsCount'], 'total' => $paginationInfo['totalItems']])); ?>

                </div>
                
                <!-- Load More Button -->
                <?php if($paginationInfo['showLoadMore']): ?>
                    <button wire:click="loadMorePage" 
                            wire:loading.attr="disabled"
                            wire:target="loadMorePage"
                            class="bg-black text-white border-2 border-black px-6 py-3 hover:bg-white hover:text-black font-black transition-all disabled:opacity-50">
                        <span wire:loading.remove wire:target="loadMorePage"><?php echo e(__('general.show_more_count', ['count' => 25])); ?></span>
                        <span wire:loading wire:target="loadMorePage"><?php echo e(__('general.loading')); ?></span>
                    </button>
                <?php else: ?>
                    <div class="text-sm font-bold text-gray-500">
                        <?php echo e(__('general.all_loaded')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Mobile: Stacked Layout -->
        <div class="flex md:hidden flex-col gap-4 bg-white border-4 border-black p-4 mb-8">
            
            <!-- Page Info & Progress -->
            <div class="flex justify-between items-center bg-black text-white py-2 px-4 font-bold text-sm">
                <span><?php echo e(__('general.page_of', ['current' => $products->currentPage(), 'total' => $products->lastPage()])); ?></span>
                <span><?php echo e($paginationInfo['currentItemsCount']); ?>/<?php echo e($paginationInfo['totalItems']); ?></span>
            </div>
            
            <!-- Navigation Controls -->
            <div class="flex justify-between items-center gap-2">
                <?php if($products->onFirstPage()): ?>
                    <span class="border-2 border-gray-300 px-3 py-2 text-gray-400 cursor-not-allowed font-bold text-sm">
                        ← <?php echo e(__('general.prev')); ?>

                    </span>
                <?php else: ?>
                    <button wire:click="previousPage" 
                            class="border-2 border-black px-3 py-2 hover:bg-black hover:text-white font-bold transition-all text-sm">
                        ← <?php echo e(__('general.prev')); ?>

                    </button>
                <?php endif; ?>
                
                <!-- Quick page numbers -->
                <div class="flex items-center gap-1">
                    <?php $__currentLoopData = $products->getUrlRange(1, min(3, $products->lastPage())); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $products->currentPage()): ?>
                            <span class="bg-black text-white border-2 border-black px-2 py-1 font-bold text-xs">
                                <?php echo e($page); ?>

                            </span>
                        <?php else: ?>
                            <button wire:click="gotoPage(<?php echo e($page); ?>)" 
                                    class="border-2 border-black px-2 py-1 hover:bg-black hover:text-white font-bold transition-all text-xs">
                                <?php echo e($page); ?>

                            </button>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                
                <?php if($products->hasMorePages()): ?>
                    <button wire:click="nextPage" 
                            class="border-2 border-black px-3 py-2 hover:bg-black hover:text-white font-bold transition-all text-sm">
                        <?php echo e(__('general.next')); ?> →
                    </button>
                <?php else: ?>
                    <span class="border-2 border-gray-300 px-3 py-2 text-gray-400 cursor-not-allowed font-bold text-sm">
                        <?php echo e(__('general.next')); ?> →
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Load More Button (Full Width) -->
            <?php if($paginationInfo['showLoadMore']): ?>
                <button wire:click="loadMorePage" 
                        wire:loading.attr="disabled"
                        wire:target="loadMorePage"
                        class="bg-black text-white border-2 border-black py-3 hover:bg-white hover:text-black font-black transition-all w-full disabled:opacity-50">
                    <span wire:loading.remove wire:target="loadMorePage"><?php echo e(__('general.show_more_products', ['count' => 25])); ?></span>
                    <span wire:loading wire:target="loadMorePage"><?php echo e(__('general.loading')); ?></span>
                </button>
            <?php else: ?>
                <div class="text-center text-gray-500 font-bold py-2">
                    <?php echo e(__('general.all_products_loaded')); ?>

                </div>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
</div>

<style>
    .pagination-btn {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hybrid-pagination-container .pagination-btn:hover {
        transform: translateY(-2px);
        box-shadow: 4px 4px 0 black;
    }
    
    @media (max-width: 768px) {
        .pagination-btn {
            min-width: 40px;
            min-height: 40px;
            font-size: 14px;
        }
    }
</style><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/pagination/hybrid-pagination.blade.php ENDPATH**/ ?>