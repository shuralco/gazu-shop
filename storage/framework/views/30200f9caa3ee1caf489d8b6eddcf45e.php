<?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center items-center gap-2">
        
        <?php if($paginator->onFirstPage()): ?>
            <span class="pagination-btn opacity-50 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </span>
        <?php else: ?>
            <button wire:click="previousPage" rel="prev" class="pagination-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        <?php endif; ?>

        
        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            
            <?php if(is_string($element)): ?>
                <span class="px-2 font-black"><?php echo e($element); ?></span>
            <?php endif; ?>

            
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="pagination-btn active"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <button wire:click="gotoPage(<?php echo e($page); ?>)" class="pagination-btn"><?php echo e($page); ?></button>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <button wire:click="nextPage" rel="next" class="pagination-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        <?php else: ?>
            <span class="pagination-btn opacity-50 cursor-not-allowed">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        <?php endif; ?>
    </nav>

    <style>
    .pagination-btn {
        border: 2px solid black;
        padding: 8px 16px;
        font-weight: 900;
        background: white;
        color: black;
        transition: all 0.2s ease;
        min-width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination-btn:hover:not(.active):not(.opacity-50) {
        background: black;
        color: white;
    }

    .pagination-btn.active {
        background: black;
        color: white;
    }
    </style>
<?php endif; ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/pagination/brutal-pagination.blade.php ENDPATH**/ ?>