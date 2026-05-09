/**
 * Header functionality for SimpleShop
 * Handles mega menu interactions, mobile menu, and dynamic header features
 */

class HeaderManager {
    constructor() {
        this.isMainMegaMenuOpen = false;
        this.isHorizontalMegaMenuOpen = false;
        this.init();
    }

    init() {
        this.initMainMegaMenu();
        this.initHorizontalMegaMenu();
        this.initMobileMenu();
        this.setupEventListeners();
    }

    initMainMegaMenu() {
        const catalogBtn = document.getElementById('catalogBtn');
        const megaMenu = document.getElementById('megaMenu');
        const catalogArrow = document.getElementById('catalogArrow');

        if (!catalogBtn || !megaMenu) return;

        // Read trigger mode from data attribute (click, hover, both)
        const headerEl = catalogBtn.closest('[data-catalog-trigger]');
        const triggerMode = headerEl ? headerEl.dataset.catalogTrigger : 'click';

        // Clone to remove old event listeners
        const newCatalogBtn = catalogBtn.cloneNode(true);
        catalogBtn.parentNode.replaceChild(newCatalogBtn, catalogBtn);

        // Click handler (always active for click and both modes)
        if (triggerMode === 'click' || triggerMode === 'both') {
            newCatalogBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleMainMegaMenu();
            });
        }

        // Hover handlers (only for hover and both modes)
        if (triggerMode === 'hover' || triggerMode === 'both') {
            newCatalogBtn.addEventListener('mouseenter', () => {
                if (window.innerWidth >= 1024) {
                    this.showMainMegaMenu();
                }
            });

            megaMenu.addEventListener('mouseenter', () => {
                if (window.innerWidth >= 1024) {
                    clearTimeout(this._megaMenuHideTimer);
                }
            });

            megaMenu.addEventListener('mouseleave', () => {
                if (window.innerWidth >= 1024) {
                    this._megaMenuHideTimer = setTimeout(() => {
                        this.hideMainMegaMenu();
                    }, 150);
                }
            });

            newCatalogBtn.addEventListener('mouseleave', () => {
                if (window.innerWidth >= 1024) {
                    this._megaMenuHideTimer = setTimeout(() => {
                        if (!megaMenu.matches(':hover')) {
                            this.hideMainMegaMenu();
                        }
                    }, 150);
                }
            });
        }
    }

    initHorizontalMegaMenu() {
        const horizontalCatalogBtn = document.getElementById('horizontalCatalogBtn');
        const horizontalMegaMenu = document.getElementById('horizontalMegaMenu');
        const horizontalCatalogArrow = document.getElementById('horizontalCatalogArrow');
        
        if (!horizontalCatalogBtn || !horizontalMegaMenu) return;
        
        // Clone to remove old event listeners
        const newHorizontalCatalogBtn = horizontalCatalogBtn.cloneNode(true);
        horizontalCatalogBtn.parentNode.replaceChild(newHorizontalCatalogBtn, horizontalCatalogBtn);
        
        // Toggle menu on click
        newHorizontalCatalogBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleHorizontalMegaMenu();
        });
        
        // Show menu on hover (desktop only)
        newHorizontalCatalogBtn.addEventListener('mouseenter', () => {
            if (window.innerWidth >= 1024) {
                this.showHorizontalMegaMenu();
            }
        });
        
        // Keep menu open when hovering over it
        horizontalMegaMenu.addEventListener('mouseenter', () => {
            if (window.innerWidth >= 1024) {
                this.showHorizontalMegaMenu();
            }
        });
        
        // Hide menu when leaving both button and menu
        horizontalMegaMenu.addEventListener('mouseleave', () => {
            if (window.innerWidth >= 1024) {
                setTimeout(() => {
                    if (!horizontalMegaMenu.matches(':hover') && !newHorizontalCatalogBtn.matches(':hover')) {
                        this.hideHorizontalMegaMenu();
                    }
                }, 500);
            }
        });

        newHorizontalCatalogBtn.addEventListener('mouseleave', () => {
            if (window.innerWidth >= 1024) {
                setTimeout(() => {
                    if (!horizontalMegaMenu.matches(':hover') && !newHorizontalCatalogBtn.matches(':hover')) {
                        this.hideHorizontalMegaMenu();
                    }
                }, 500);
            }
        });
    }

    initMobileMenu() {
        const openMobileMenuBtn = document.getElementById('openMobileMenu');
        const closeMobileMenuBtn = document.getElementById('closeMobileMenu');
        const mobileOverlay = document.getElementById('mobileOverlay');
        const mobileMenuPanel = document.getElementById('mobileMenuPanel');
        
        if (openMobileMenuBtn && mobileMenuPanel) {
            openMobileMenuBtn.addEventListener('click', () => {
                this.showMobileMenu();
            });
        }
        
        if (closeMobileMenuBtn) {
            closeMobileMenuBtn.addEventListener('click', () => {
                this.hideMobileMenu();
            });
        }
        
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', () => {
                this.hideMobileMenu();
            });
        }
        
        // Mobile category toggles
        const categoryToggles = document.querySelectorAll('.mobile-category-toggle');
        categoryToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                const targetId = toggle.getAttribute('data-target');
                const submenu = document.getElementById(targetId);
                const arrow = toggle.querySelector('svg');
                
                if (submenu) {
                    submenu.classList.toggle('hidden');
                    if (arrow) {
                        arrow.style.transform = submenu.classList.contains('hidden') 
                            ? 'rotate(0deg)' 
                            : 'rotate(180deg)';
                    }
                }
            });
        });
    }

    setupEventListeners() {
        // Close menus when clicking outside
        document.addEventListener('click', (e) => {
            const catalogBtn = document.getElementById('catalogBtn');
            const megaMenu = document.getElementById('megaMenu');
            const horizontalCatalogBtn = document.getElementById('horizontalCatalogBtn');
            const horizontalMegaMenu = document.getElementById('horizontalMegaMenu');
            
            // Close main mega menu
            if (catalogBtn && megaMenu && 
                !catalogBtn.contains(e.target) && 
                !megaMenu.contains(e.target)) {
                this.hideMainMegaMenu();
            }
            
            // Close horizontal mega menu
            if (horizontalCatalogBtn && horizontalMegaMenu && 
                !horizontalCatalogBtn.contains(e.target) && 
                !horizontalMegaMenu.contains(e.target)) {
                this.hideHorizontalMegaMenu();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.hideMobileMenu();
            }
        });
        
        // Escape key to close menus
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideMainMegaMenu();
                this.hideHorizontalMegaMenu();
                this.hideMobileMenu();
            }
        });
    }

    // Main Mega Menu Methods
    toggleMainMegaMenu() {
        this.isMainMegaMenuOpen ? this.hideMainMegaMenu() : this.showMainMegaMenu();
    }

    showMainMegaMenu() {
        const megaMenu = document.getElementById('megaMenu');
        const catalogArrow = document.getElementById('catalogArrow');
        
        if (megaMenu) {
            megaMenu.classList.add('active');
            this.isMainMegaMenuOpen = true;
        }
        
        if (catalogArrow) {
            catalogArrow.style.transform = 'rotate(180deg)';
        }
    }

    hideMainMegaMenu() {
        const megaMenu = document.getElementById('megaMenu');
        const catalogArrow = document.getElementById('catalogArrow');
        
        if (megaMenu) {
            megaMenu.classList.remove('active');
            this.isMainMegaMenuOpen = false;
        }
        
        if (catalogArrow) {
            catalogArrow.style.transform = 'rotate(0deg)';
        }
    }

    // Horizontal Mega Menu Methods
    toggleHorizontalMegaMenu() {
        this.isHorizontalMegaMenuOpen ? this.hideHorizontalMegaMenu() : this.showHorizontalMegaMenu();
    }

    showHorizontalMegaMenu() {
        const horizontalMegaMenu = document.getElementById('horizontalMegaMenu');
        const horizontalCatalogArrow = document.getElementById('horizontalCatalogArrow');
        
        if (horizontalMegaMenu) {
            horizontalMegaMenu.classList.add('active');
            this.isHorizontalMegaMenuOpen = true;
        }
        
        if (horizontalCatalogArrow) {
            horizontalCatalogArrow.style.transform = 'rotate(180deg)';
        }
    }

    hideHorizontalMegaMenu() {
        const horizontalMegaMenu = document.getElementById('horizontalMegaMenu');
        const horizontalCatalogArrow = document.getElementById('horizontalCatalogArrow');
        
        if (horizontalMegaMenu) {
            horizontalMegaMenu.classList.remove('active');
            this.isHorizontalMegaMenuOpen = false;
        }
        
        if (horizontalCatalogArrow) {
            horizontalCatalogArrow.style.transform = 'rotate(0deg)';
        }
    }

    // Mobile Menu Methods
    showMobileMenu() {
        const mobileOverlay = document.getElementById('mobileOverlay');
        const mobileMenuPanel = document.getElementById('mobileMenuPanel');
        
        if (mobileOverlay) {
            mobileOverlay.classList.add('active');
        }
        
        if (mobileMenuPanel) {
            mobileMenuPanel.classList.add('active');
        }
        
        document.body.style.overflow = 'hidden';
    }

    hideMobileMenu() {
        const mobileOverlay = document.getElementById('mobileOverlay');
        const mobileMenuPanel = document.getElementById('mobileMenuPanel');
        
        if (mobileOverlay) {
            mobileOverlay.classList.remove('active');
        }
        
        if (mobileMenuPanel) {
            mobileMenuPanel.classList.remove('active');
        }
        
        document.body.style.overflow = '';
    }

    // Public method to reinitialize after Livewire navigation
    reinitialize() {
        this.isMainMegaMenuOpen = false;
        this.isHorizontalMegaMenuOpen = false;
        this.init();
    }
}

// Initialize header manager
let headerManager;

document.addEventListener('DOMContentLoaded', function() {
    headerManager = new HeaderManager();
});

// Re-initialize after Livewire navigation
document.addEventListener('livewire:navigated', () => {
    if (headerManager) {
        headerManager.reinitialize();
    } else {
        headerManager = new HeaderManager();
    }
});

// Export for global access
window.HeaderManager = HeaderManager;
window.headerManager = headerManager;