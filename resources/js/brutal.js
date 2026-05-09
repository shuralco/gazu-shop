// ================================================
// BRUTAL MINIMALISM JAVASCRIPT FOR SIMPLESHOP
// ================================================

document.addEventListener('DOMContentLoaded', function() {
    // Scroll Progress Bar
    window.addEventListener('scroll', () => {
        const scrollProgress = document.getElementById('scrollProgress');
        if (scrollProgress) {
            const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPosition = window.scrollY;
            const progress = (scrollPosition / scrollHeight) * 100;
            scrollProgress.style.height = progress + '%';
        }
    });
    
    // Desktop Mega Menu
    const catalogBtn = document.getElementById('catalogBtn');
    const megaMenu = document.getElementById('megaMenu');
    const catalogArrow = document.getElementById('catalogArrow');
    let megaMenuTimeout;
    
    if (catalogBtn && megaMenu && catalogArrow) {
        catalogBtn.addEventListener('mouseenter', () => {
            clearTimeout(megaMenuTimeout);
            megaMenu.classList.add('active');
            catalogArrow.style.transform = 'rotate(180deg)';
        });
        
        catalogBtn.addEventListener('mouseleave', () => {
            megaMenuTimeout = setTimeout(() => {
                megaMenu.classList.remove('active');
                catalogArrow.style.transform = 'rotate(0deg)';
            }, 300);
        });
        
        megaMenu.addEventListener('mouseenter', () => {
            clearTimeout(megaMenuTimeout);
        });
        
        megaMenu.addEventListener('mouseleave', () => {
            megaMenu.classList.remove('active');
            catalogArrow.style.transform = 'rotate(0deg)';
        });
    }
    
    // Mobile Menu
    const openMobileMenu = document.getElementById('openMobileMenu');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const mobileMenuPanel = document.getElementById('mobileMenuPanel');
    const mobileOverlay = document.getElementById('mobileOverlay');
    
    if (openMobileMenu && closeMobileMenu && mobileMenuPanel && mobileOverlay) {
        openMobileMenu.addEventListener('click', () => {
            mobileMenuPanel.classList.add('active');
            mobileOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeMobileMenu.addEventListener('click', () => {
            mobileMenuPanel.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        mobileOverlay.addEventListener('click', () => {
            mobileMenuPanel.classList.remove('active');
            mobileOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// Toggle Submenu Function for Mobile
function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    if (submenu) {
        submenu.classList.toggle('active');
    }
}

// Global function for category wall navigation
window.goToCategory = function(categorySlug) {
    window.location.href = '/category/' + categorySlug;
};

// Cart Modal Functions - Global Access
window.openCartModal = function() {
    console.log('openCartModal called');
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        console.log('Cart modal opened');
    } else {
        console.error('Cart modal not found');
    }
};

window.closeCartModal = function() {
    const modal = document.getElementById('cartModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
};

// Navigation Functions
window.goToCart = function() {
    window.location.href = '/cart';
    window.closeCartModal();
};

window.goToCheckout = function() {
    window.location.href = '/checkout';
    window.closeCartModal();
};

// Cart Modal Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('cartModal');
    
    // Close on click outside (on backdrop)
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                window.closeCartModal();
            }
        });
    }
    
    // Keyboard Support
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
            window.closeCartModal();
        }
    });
});

// Livewire події
window.addEventListener('open-cart', event => {
    window.openCartModal();
});