/**
 * Mega Menu Admin functionality
 * Handles drag & drop, sortable functionality for mega menu management
 */

class MegaMenuAdmin {
    constructor() {
        this.init();
    }

    init() {
        this.initSortable();
        this.setupEventListeners();
    }

    initSortable() {
        // Initialize Sortable for columns
        const columnsContainer = document.querySelector('.mega-columns-container');
        if (columnsContainer && typeof Sortable !== 'undefined') {
            new Sortable(columnsContainer, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: (evt) => {
                    // Update column order via Livewire
                    if (window.Livewire) {
                        const component = Livewire.find(columnsContainer.closest('[wire\\:id]').getAttribute('wire:id'));
                        if (component) {
                            component.call('reorderColumns', evt.oldIndex, evt.newIndex);
                        }
                    }
                }
            });
        }

        // Initialize Sortable for items within columns
        document.querySelectorAll('.mega-column').forEach(column => {
            const itemsContainer = column.querySelector('.column-items');
            if (itemsContainer && typeof Sortable !== 'undefined') {
                new Sortable(itemsContainer, {
                    group: 'shared',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: (evt) => {
                        // Update item order via Livewire
                        const sourceColumn = evt.from.closest('[data-column]').dataset.column;
                        const targetColumn = evt.to.closest('[data-column]').dataset.column;
                        
                        if (window.Livewire) {
                            const component = Livewire.find(column.closest('[wire\\:id]').getAttribute('wire:id'));
                            if (component) {
                                component.call('moveItem', sourceColumn, evt.oldIndex, targetColumn, evt.newIndex);
                            }
                        }
                    }
                });
            }
        });
    }

    setupEventListeners() {
        // Reinitialize after Livewire updates
        document.addEventListener('livewire:navigated', () => {
            this.init();
        });

        // Handle dynamic content updates
        if (window.Livewire) {
            Livewire.hook('morph.updated', () => {
                this.initSortable();
            });
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.mega-menu-admin')) {
        window.megaMenuAdmin = new MegaMenuAdmin();
    }
});

// Also initialize after Livewire navigation
document.addEventListener('livewire:navigated', function() {
    if (document.querySelector('.mega-menu-admin') && !window.megaMenuAdmin) {
        window.megaMenuAdmin = new MegaMenuAdmin();
    }
});