<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\On;
use Livewire\Component;

class NotificationManager extends Component
{
    public array $notifications = [];

    public bool $cartModalOpen = false;

    public array $queuedNotifications = [];

    /**
     * Fix for Livewire 3 toJSON error.
     * This method is called by JavaScript when trying to serialize the component.
     */
    public function toJSON(): string
    {
        return json_encode([
            'notificationsCount' => count($this->notifications),
            'queuedCount' => count($this->queuedNotifications),
            'cartModalOpen' => $this->cartModalOpen,
            'componentName' => 'notification-manager',
        ]);
    }

    public function mount(): void
    {
        $route = request()->route();
        $currentRoute = $route ? $route->getName() : null;

        // Очистити всі повідомлення на checkout сторінці
        if ($currentRoute === 'checkout') {
            session()->forget('notifications');
            session()->forget('queued_notifications');
            $this->notifications = [];
            $this->queuedNotifications = [];
        } else {
            $notifications = session('notifications', []);
            $queuedNotifications = session('queued_notifications', []);

            $this->notifications = is_array($notifications) ? $notifications : [];
            $this->queuedNotifications = is_array($queuedNotifications) ? $queuedNotifications : [];

            // Skip expensive cleanup operations for better performance
            // $this->cleanupOldNotifications();
            // $this->limitNotifications();
        }
    }

    private function cleanupOldNotifications(): void
    {
        // Skip expensive cleanup if no notifications exist
        if (empty($this->notifications) && empty($this->queuedNotifications)) {
            return;
        }

        $cutoffTimestamp = now()->subMinutes(2)->timestamp;

        // Fast array_filter using timestamp comparison instead of Carbon parsing
        $this->notifications = array_filter($this->notifications, function ($notification) use ($cutoffTimestamp) {
            if (! isset($notification['created_at'])) {
                return false; // Remove notifications without timestamp
            }

            $timestamp = is_string($notification['created_at']) ?
                strtotime($notification['created_at']) :
                $notification['created_at']->timestamp ?? 0;

            return $timestamp >= $cutoffTimestamp;
        });

        $this->queuedNotifications = array_filter($this->queuedNotifications, function ($notification) use ($cutoffTimestamp) {
            if (! isset($notification['created_at'])) {
                return false;
            }

            $timestamp = is_string($notification['created_at']) ?
                strtotime($notification['created_at']) :
                $notification['created_at']->timestamp ?? 0;

            return $timestamp >= $cutoffTimestamp;
        });

        // Batch session updates
        if (empty($this->notifications)) {
            session()->forget('notifications');
        } else {
            session()->put('notifications', $this->notifications);
        }

        if (empty($this->queuedNotifications)) {
            session()->forget('queued_notifications');
        } else {
            session()->put('queued_notifications', $this->queuedNotifications);
        }
    }

    private function limitNotifications(): void
    {
        // Limit to maximum 3 notifications to prevent UI clutter
        if (count($this->notifications) > 3) {
            $this->notifications = array_slice($this->notifications, -3, 3, true);
            session()->put('notifications', $this->notifications);
        }

        if (count($this->queuedNotifications) > 3) {
            $this->queuedNotifications = array_slice($this->queuedNotifications, -3, 3, true);
            session()->put('queued_notifications', $this->queuedNotifications);
        }
    }

    public function shouldShowAsToast(): bool
    {
        $route = request()->route();
        $currentRoute = $route ? $route->getName() : null;

        // На сторінках категорій та продуктів показувати тільки toast
        return $currentRoute && in_array($currentRoute, [
            'category.show',
            'product.show',
            'search.results',
            'home',
        ]);
    }

    public function shouldShowNotifications(): bool
    {
        $route = request()->route();
        $currentRoute = $route ? $route->getName() : null;

        // Приховати повідомлення на checkout сторінці
        return $currentRoute !== 'checkout';
    }

    #[On('livewire:navigated')]
    public function handleNavigation(): void
    {
        $this->mount(); // Re-run mount logic on navigation
    }

    #[On('cart-modal-opened')]
    public function handleCartModalOpened(): void
    {
        $this->cartModalOpen = true;

        // Move current notifications to queue
        if (! empty($this->notifications)) {
            $this->queuedNotifications = array_merge($this->queuedNotifications, $this->notifications);
            $this->notifications = [];
            session()->put('notifications', []);
            session()->put('queued_notifications', $this->queuedNotifications);
        }
    }

    #[On('cart-modal-closed')]
    public function handleCartModalClosed(): void
    {
        $this->cartModalOpen = false;

        // Restore queued notifications
        if (! empty($this->queuedNotifications)) {
            $this->notifications = $this->queuedNotifications;
            $this->queuedNotifications = [];
            session()->put('notifications', $this->notifications);
            session()->forget('queued_notifications');
        }
    }

    #[On('show-notification')]
    public function showNotification(string $type, string $title, string $message, ?string $action = null, ?string $actionUrl = null): void
    {
        $notification = [
            'id' => uniqid(),
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action' => $action,
            'actionUrl' => $actionUrl,
            'timestamp' => now()->format('H:i'),
            'created_at' => now(),
            'auto_hide_seconds' => $this->getAutoHideTime($type),
        ];

        // If cart modal is open, queue the notification instead of showing it
        if ($this->cartModalOpen) {
            $this->queuedNotifications[] = $notification;
            session()->put('queued_notifications', $this->queuedNotifications);
        } else {
            $this->notifications[] = $notification;
            session()->put('notifications', $this->notifications);
            $this->dispatch('notification-added', $notification);

            // Also trigger browser event for JavaScript handling
            $this->js("window.dispatchEvent(new CustomEvent('notification-added'))");
        }
    }

    public function removeNotification(string $id): void
    {
        $this->notifications = array_filter($this->notifications, fn ($notification) => $notification['id'] !== $id);
        session()->put('notifications', $this->notifications);

        // Оптимізація: перевірити чи потрібно очистити сесію
        if (empty($this->notifications)) {
            session()->forget('notifications');
        }
    }

    public function clearAll(): void
    {
        $this->notifications = [];
        $this->queuedNotifications = [];
        session()->forget('notifications');
        session()->forget('queued_notifications');
    }

    public function getIcon(string $type): string
    {
        return match ($type) {
            'success' => '✅',
            'error' => '❌',
            'warning' => '⚠️',
            'info' => '🔔',
            'purple' => '📦',
            default => 'ℹ️'
        };
    }

    public function getBadgeClass(string $type): string
    {
        return match ($type) {
            'success' => 'accent-success',
            'error' => 'accent-error',
            'warning' => 'accent-warning',
            'info' => 'accent-info',
            'purple' => 'accent-purple',
            default => 'accent-info'
        };
    }

    private function getAutoHideTime(string $type): int
    {
        return match ($type) {
            'success' => 1,
            'error' => 6,
            'warning' => 4,
            'info' => 3,
            'purple' => 4,
            default => 3
        };
    }

    public function render(): mixed
    {
        return view('livewire.notifications.notification-manager');
    }
}
