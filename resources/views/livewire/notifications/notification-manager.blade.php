<div>
    <!-- Toast Container -->
    @if($this->shouldShowNotifications())
    <div class="notification-container" wire:ignore.self style="{{ $cartModalOpen ? 'display: none !important;' : '' }}">
        @foreach($notifications as $notification)
                <!-- Toast Notification -->
                <div class="notification-toast" 
                     id="notification-{{ $notification['id'] }}" 
                     wire:key="notification-{{ $notification['id'] }}"
                     data-auto-hide="{{ $notification['auto_hide_seconds'] ?? 5 }}">
                    <div class="flex items-start gap-3">
                        <div class="text-2xl">{!! $this->getIcon($notification['type']) !!}</div>
                        <div class="flex-1">
                            <div class="{{ $this->getBadgeClass($notification['type']) }} inline-block px-3 py-1 text-xs font-800 mb-2">
                                @switch($notification['type'])
                                    @case('success')
                                        УСПІХ
                                        @break
                                    @case('error')
                                        ПОМИЛКА
                                        @break
                                    @case('warning')
                                        ПОПЕРЕДЖЕННЯ
                                        @break
                                    @case('info')
                                        ІНФОРМАЦІЯ
                                        @break
                                    @case('purple')
                                        СПЕЦІАЛЬНА ПРОПОЗИЦІЯ
                                        @break
                                    @default
                                        ПОВІДОМЛЕННЯ
                                @endswitch
                            </div>
                            <p class="font-800 text-black mb-1">{{ strtoupper($notification['title']) }}</p>
                            <p class="text-sm text-gray-600">{{ $notification['message'] }}</p>
                            @if($notification['action'] && $notification['actionUrl'])
                                @if($notification['actionUrl'] === locale_route('cart'))
                                    <button 
                                        onclick="window.openCartModal()"
                                        class="btn-action mt-2"
                                    >
                                        {{ __('general.view_cart') }}
                                    </button>
                                @else
                                    <button 
                                        wire:click="$dispatch('navigate', { url: '{{ $notification['actionUrl'] }}' })"
                                        class="btn-action mt-2"
                                    >
                                        {{ strtoupper($notification['action']) }}
                                    </button>
                                @endif
                            @endif
                        </div>
                        <button 
                            wire:click="removeNotification('{{ $notification['id'] }}')"
                            class="text-xl font-800 hover:bg-gray-100 w-6 h-6 flex items-center justify-center"
                        >×</button>
                    </div>
                </div>
        @endforeach
    </div>
    @endif

    <!-- Banner Notifications for persistent messages -->
    @if(false)
                <div class="notification-banner" wire:key="banner-{{ $notification['id'] }}">
                <div class="flex items-center gap-4">
                    <div class="{{ $this->getBadgeClass($notification['type']) }} px-3 py-2 text-xs font-800">
                        @switch($notification['type'])
                            @case('success')
                                УСПІХ
                                @break
                            @case('error')
                                ПОМИЛКА
                                @break
                            @case('warning')
                                ПОПЕРЕДЖЕННЯ
                                @break
                            @case('info')
                                ІНФОРМАЦІЯ
                                @break
                            @case('purple')
                                СПЕЦІАЛЬНА ПРОПОЗИЦІЯ
                                @break
                            @default
                                ПОВІДОМЛЕННЯ
                        @endswitch
                    </div>
                    <div class="flex-1">
                        <p class="font-800">{!! $this->getIcon($notification['type']) !!} {{ strtoupper($notification['title']) }}</p>
                        <p class="text-sm text-gray-600">{{ $notification['message'] }}</p>
                    </div>
                    @if($notification['action'] && $notification['actionUrl'])
                        @if($notification['actionUrl'] === locale_route('cart'))
                            <button 
                                onclick="window.openCartModal()"
                                class="btn-solid"
                            >
                                {{ __('general.view_cart') }}
                            </button>
                        @else
                            <button 
                                wire:click="$dispatch('navigate', { url: '{{ $notification['actionUrl'] }}' })"
                                class="btn-solid"
                            >
                                {{ strtoupper($notification['action']) }}
                            </button>
                        @endif
                    @endif
                    <button 
                        wire:click="removeNotification('{{ $notification['id'] }}')"
                        class="btn-solid"
                    >×</button>
                </div>
            </div>
            @endif
        @endforeach
    @endif

    <style>
        .notification-container {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 99999 !important;
            pointer-events: none;
        }
        
        /* Force hide notifications on checkout page */
        body[data-route="checkout"] .notification-container,
        body[data-route="checkout"] .notification-banner {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }
        
        .notification-toast {
            max-width: 400px;
            border: 4px solid black;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            margin-bottom: 16px;
            animation: slideIn 0.3s ease;
            pointer-events: auto;
            z-index: 99998 !important;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .notification-removing {
            animation: slideOut 0.3s ease forwards !important;
        }
        
        .notification-banner {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 99997 !important;
            max-width: 400px;
            width: auto;
            border: 4px solid black;
            background: white;
            padding: 16px 24px;
            margin-bottom: 16px;
            animation: slideIn 0.3s ease;
            pointer-events: auto;
        }
        
        .accent-success { background: #dcfce7; color: #16a34a; }
        .accent-error { background: #fef2f2; color: #dc2626; }
        .accent-warning { background: #fff7ed; color: #ea580c; }
        .accent-info { background: #eff6ff; color: #2563eb; }
        .accent-purple { background: #faf5ff; color: #9333ea; }
        
        .btn-solid {
            border: 2px solid black;
            padding: 8px 16px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            font-size: 12px;
        }
        
        .btn-solid:hover {
            background: black;
            color: white;
        }
        
        .btn-action {
            border: 2px solid black;
            padding: 6px 12px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            font-size: 11px;
        }
        
        .btn-action:hover {
            background: black;
            color: white;
        }
    </style>

    <script>
        // Simplified notification auto-hide system
        document.addEventListener('livewire:init', () => {
            let activeTimeouts = new Map();
            
            // Simple auto-hide function
            function setupNotificationAutoHide(element) {
                if (!element?.id) return;
                
                const autoHideSeconds = parseInt(element.dataset.autoHide || '3');
                const notificationId = element.id;
                
                // Clear any existing timeout
                if (activeTimeouts.has(notificationId)) {
                    clearTimeout(activeTimeouts.get(notificationId));
                }
                
                // Set new timeout
                const timeoutId = setTimeout(() => {
                    if (element.parentNode) {
                        // Add fade-out class
                        element.classList.add('notification-removing');
                        
                        // Remove from DOM after animation
                        setTimeout(() => {
                            if (element.parentNode) {
                                element.remove();
                                
                                // Also remove from backend
                                const id = notificationId.replace('notification-', '');
                                const container = document.querySelector('.notification-container');
                                if (container && window.Livewire) {
                                    const wireEl = container.closest('[wire\\:id]');
                                    if (wireEl) {
                                        const component = Livewire.find(wireEl.getAttribute('wire:id'));
                                        if (component) {
                                            component.call('removeNotification', id);
                                        }
                                    }
                                }
                            }
                            activeTimeouts.delete(notificationId);
                        }, 300);
                    }
                }, autoHideSeconds * 1000);
                
                activeTimeouts.set(notificationId, timeoutId);
            }
            
            // Setup auto-hide for existing notifications
            function setupAllNotifications() {
                document.querySelectorAll('.notification-toast').forEach(setupNotificationAutoHide);
            }
            
            // Initial setup
            setupAllNotifications();
            
            // Handle new notifications after Livewire updates
            window.addEventListener('livewire:navigated', () => {
                setTimeout(setupAllNotifications, 50);
            });
            
            // Listen for notification events
            window.addEventListener('notification-added', () => {
                setTimeout(setupAllNotifications, 50);
            });
        });
    </script>
</div>