/**
 * GAZU FX — глобальні анімації + Toast store + SPA-like navigation.
 * Ініціалізує Alpine.store('toast') і інтегрується з View Transitions API.
 */
(function () {
    'use strict';

    // ─────────────────────────────────────
    // Alpine: глобальний Toast store
    // ─────────────────────────────────────
    document.addEventListener('alpine:init', function () {
        if (!window.Alpine) return;

        Alpine.store('toast', {
            queue: [],
            id: 0,
            show(message, type, opts) {
                type = type || 'success';
                opts = opts || {};
                var id = ++this.id;
                // Use reassignment for Alpine reactivity (push() doesn't always trigger)
                this.queue = [...this.queue, { id: id, message: message, type: type, action: opts.action || null }];
                setTimeout(() => this.dismiss(id), opts.duration || 3500);
            },
            success(m, opts) { this.show(m, 'success', opts); },
            error(m, opts)   { this.show(m, 'error', opts); },
            info(m, opts)    { this.show(m, 'info', opts); },
            dismiss(id) {
                this.queue = this.queue.filter(t => t.id !== id);
            },
        });
    });

    // ─────────────────────────────────────
    // Bridge: window.gazuToast(...) — для не-Alpine коду
    // ─────────────────────────────────────
    window.gazuToast = function (message, type, opts) {
        if (window.Alpine && Alpine.store('toast')) {
            Alpine.store('toast').show(message, type, opts);
        }
    };

    // ─────────────────────────────────────
    // Cart icon bounce — слухає custom event 'cart-updated'
    // ─────────────────────────────────────
    document.addEventListener('cart-updated', function () {
        var cartIcon = document.querySelector('[data-gazu-cart-icon]');
        if (!cartIcon) return;
        cartIcon.classList.remove('gazu-bounce');
        // force reflow
        void cartIcon.offsetWidth;
        cartIcon.classList.add('gazu-bounce');
    });

    // ─────────────────────────────────────
    // SPA-like navigation через View Transitions API
    // ─────────────────────────────────────
    if ('startViewTransition' in document) {
        document.documentElement.classList.add('gazu-vt-supported');

        document.addEventListener('click', function (e) {
            var link = e.target.closest && e.target.closest('a[href]');
            if (!link) return;
            // Виключаємо: external, _blank, hash, JS-handlers
            var url;
            try { url = new URL(link.href, location.href); } catch (_) { return; }
            if (url.origin !== location.origin) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (link.hasAttribute('data-no-spa')) return;
            if (link.getAttribute('href').startsWith('#')) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

            e.preventDefault();
            navigateSPA(url.href);
        });

        window.addEventListener('popstate', function () {
            navigateSPA(location.href, true);
        });
    }

    var spaCache = new Map();
    function navigateSPA(url, isPopState) {
        var transition = document.startViewTransition(async function () {
            var html;
            try {
                if (spaCache.has(url)) {
                    html = spaCache.get(url);
                } else {
                    var resp = await fetch(url, { headers: { 'X-Requested-With': 'gazu-spa' } });
                    if (!resp.ok || !resp.headers.get('content-type')?.includes('text/html')) {
                        location.href = url; return;
                    }
                    html = await resp.text();
                    spaCache.set(url, html);
                }
            } catch (_) {
                location.href = url; return;
            }
            var doc = new DOMParser().parseFromString(html, 'text/html');
            // Заміна <main>, <title> та scrollTop
            var newMain = doc.querySelector('main');
            var oldMain = document.querySelector('main');
            if (newMain && oldMain) oldMain.replaceWith(newMain);
            if (doc.title) document.title = doc.title;
            if (!isPopState) history.pushState({}, '', url);
            window.scrollTo({ top: 0, behavior: 'instant' });
            // Re-init Alpine на новому контенті (Alpine 3 робить це автоматично через MutationObserver)
        });
        transition.finished.catch(function () {});
    }
})();
