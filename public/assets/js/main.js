window.addEventListener('scroll', function () {
    const headerNav = document.getElementById('header-nav');
    if (headerNav) {
        headerNav.classList.toggle('headernav-scroll', window.scrollY > 135);
    }
    
    // Update scroll progress bar
    const scrollProgress = document.getElementById('scrollProgress');
    if (scrollProgress) {
        const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrollPercent = (scrollTop / scrollHeight) * 100;
        scrollProgress.style.height = scrollPercent + '%';
    }
});

document.addEventListener('livewire:navigated', () => {

    if (typeof $ !== 'undefined') {
        $('#top').click(function () {
            $('html, body').animate({ scrollTop: 0 }, 500);
            return false;
        });
    }

    if (typeof $ !== 'undefined' && typeof $.fn.owlCarousel === 'function') {
        $('.owl-carousel-full').owlCarousel('destroy');
        $(".owl-carousel-full").owlCarousel({
            margin: 20,
            responsive: {
                0: {
                    items: 1
                },
                500: {
                    items: 2
                },
                700: {
                    items: 3
                },
                1000: {
                    items: 4
                }
            }
        });
    }
});

if (typeof $ !== 'undefined') {
    $(document).ready(function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 300) {
                $('#top').fadeIn();
            } else {
                $('#top').fadeOut();
            }
        });
    });
}

// Check if toastr is available
if (typeof toastr !== 'undefined') {
    toastr.options = {
    "closeButton": false,
    "debug": false,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-bottom-right",
    "preventDuplicates": false,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "500",
    "timeOut": "4000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "slideDown",
    "hideMethod": "slideUp"
    };
}

/*window.addEventListener("popstate", function (e) {
    window.location.reload();
});*/

// Mobile Menu Functionality
document.addEventListener('DOMContentLoaded', function() {
    const openMobileMenu = document.getElementById('openMobileMenu');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const mobileMenuPanel = document.getElementById('mobileMenuPanel');
    const mobileOverlay = document.getElementById('mobileOverlay');

    function openMenu() {
        if (mobileMenuPanel) mobileMenuPanel.classList.add('active');
        if (mobileOverlay) mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        if (mobileMenuPanel) mobileMenuPanel.classList.remove('active');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (openMobileMenu) {
        openMobileMenu.addEventListener('click', openMenu);
    }

    if (closeMobileMenu) {
        closeMobileMenu.addEventListener('click', closeMenu);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMenu);
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });

    // Mobile category toggles
    document.addEventListener('click', function(e) {
        if (e.target.closest('.mobile-category-toggle')) {
            const button = e.target.closest('.mobile-category-toggle');
            const targetId = button.getAttribute('data-target');
            const submenu = document.getElementById(targetId);
            const arrow = button.querySelector('svg');
            
            if (submenu) {
                submenu.classList.toggle('hidden');
                if (arrow) {
                    arrow.style.transform = submenu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        }
    });
});

// Product Gallery Functionality
function changeMainImage(imageSrc, thumbnailElement) {
    const mainImage = document.getElementById('mainProductImage');
    if (mainImage && mainImage.tagName === 'IMG') {
        mainImage.src = imageSrc;
    }
    
    // Update thumbnail active states
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumb => {
        thumb.classList.remove('active');
        thumb.classList.remove('border-black');
        thumb.classList.add('border-gray-300');
    });
    
    if (thumbnailElement) {
        thumbnailElement.classList.add('active');
        thumbnailElement.classList.add('border-black');
        thumbnailElement.classList.remove('border-gray-300');
    }
}

// Product Tabs Functionality
function switchProductTab(tabId, buttonElement) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
        selectedTab.classList.add('active');
    }
    
    // Update button states
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('border-transparent');
        btn.classList.remove('border-black');
    });
    
    if (buttonElement) {
        buttonElement.classList.add('active');
        buttonElement.classList.add('border-black');
        buttonElement.classList.remove('border-transparent');
    }
}

// Quick Order Modal
function openQuickOrderModal() {
    const modal = document.getElementById('quickOrderModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeQuickOrderModal() {
    const modal = document.getElementById('quickOrderModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQuickOrderModal();
    }
});

// Close modal on overlay click
document.addEventListener('click', function(e) {
    if (e.target.id === 'quickOrderModal') {
        closeQuickOrderModal();
    }
});

// Cart Modal Functions - Global Access
function openCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeCartModal() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Navigation Functions
function goToCart() {
    window.location.href = '/cart';
    closeCartModal();
}

function goToCheckout() {
    window.location.href = '/checkout';
    closeCartModal();
}

// Ensure functions are available globally
window.openCartModal = openCartModal;
window.closeCartModal = closeCartModal;
window.goToCart = goToCart;
window.goToCheckout = goToCheckout;
window.changeMainImage = changeMainImage;
window.switchProductTab = switchProductTab;
window.openQuickOrderModal = openQuickOrderModal;
window.closeQuickOrderModal = closeQuickOrderModal;

// Cart Modal Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('cartModal');
    
    // Close on click outside (on backdrop)
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeCartModal();
            }
        });
    }
    
    // Keyboard Support
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            closeCartModal();
        }
    });
});

// Livewire події
window.addEventListener('open-cart', event => {
    openCartModal();
});

// Re-initialize functions after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    // Re-bind global functions
    window.openCartModal = openCartModal;
    window.closeCartModal = closeCartModal;
    window.goToCart = goToCart;
    window.goToCheckout = goToCheckout;
    window.changeMainImage = changeMainImage;
    window.switchProductTab = switchProductTab;
    window.openQuickOrderModal = openQuickOrderModal;
    window.closeQuickOrderModal = closeQuickOrderModal;
    
    // Re-initialize filter modal if exists
    if (typeof window.initFilterModal === 'function') {
        window.initFilterModal();
    }
});

// Initialize on Livewire ready
document.addEventListener('livewire:initialized', () => {
    console.log('Livewire initialized - binding functions');
    // Re-bind global functions
    window.openCartModal = openCartModal;
    window.closeCartModal = closeCartModal;
    window.goToCart = goToCart;
    window.goToCheckout = goToCheckout;
    window.changeMainImage = changeMainImage;
    window.switchProductTab = switchProductTab;
    window.openQuickOrderModal = openQuickOrderModal;
    window.closeQuickOrderModal = closeQuickOrderModal;
    
    // Debug Livewire functionality
    const reviewButtons = document.querySelectorAll('[wire\\:click*="toggleReviewForm"]');
    console.log('Found review buttons:', reviewButtons.length);
    reviewButtons.forEach((btn, index) => {
        console.log(`Review button ${index}:`, btn);
        btn.addEventListener('click', (e) => {
            console.log('Review button clicked, Livewire should handle this');
        });
    });
});
