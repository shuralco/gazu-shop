// Brutal.js - Brutalist design helper functions

// Utility functions for brutal design patterns
(function() {
    'use strict';
    
    // Global cart modal functions (also defined in main.js)
    window.openCartModal = window.openCartModal || function() {
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (typeof cartModalIsOpen !== 'undefined') {
                cartModalIsOpen = true;
            }
        }
    };
    
    window.closeCartModal = window.closeCartModal || function() {
        const modal = document.getElementById('cartModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            if (typeof cartModalIsOpen !== 'undefined') {
                cartModalIsOpen = false;
            }
        }
    };
    
    // Initialize on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Brutal.js loaded and ready');
    });
    
})();