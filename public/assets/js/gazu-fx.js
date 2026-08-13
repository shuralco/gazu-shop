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
            // Інлайн-скрипти з нового <main> браузер не виконує — сповіщаємо
            // слухачів, щоб вони переініціалізували поведінку на свіжому DOM.
            document.dispatchEvent(new CustomEvent('gazu:navigated'));
        });
        transition.finished.catch(function () {});
    }

    // ─────────────────────────────────────
    // Каталог: довантаження наступної сторінки
    // Режим приходить розміткою (assets → gazu.partials.catalog-load-more):
    // more — по кліку, infinite — автоматично при досягненні блока.
    // ─────────────────────────────────────
    function initCatalogLoadMore() {
        var box = document.getElementById('gazu-load-more');
        var grid = document.getElementById('gazu-grid');
        if (!box || !grid || box.dataset.bound === '1') return;
        box.dataset.bound = '1';

        var btn = box.querySelector('[data-gazu-load-more-btn]');
        var status = box.querySelector('[data-gazu-load-more-status]');
        var total = parseInt(box.dataset.total || '0', 10);
        var busy = false;

        // Нумерація — фолбек для no-JS; зі скриптом вона лише дублює кнопку.
        document.querySelectorAll('[data-gazu-pagination]').forEach(function (el) { el.hidden = true; });

        function setStatus() {
            if (!status) return;
            var shown = grid.children.length;
            status.textContent = total ? 'Показано ' + shown + ' з ' + total : 'Показано ' + shown;
        }

        function load() {
            var next = box.dataset.next;
            if (busy || !next) return;
            busy = true;
            if (btn) { btn.textContent = 'Завантажуємо…'; btn.setAttribute('aria-busy', 'true'); }

            fetch(next, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.text();
                })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var incoming = doc.getElementById('gazu-grid');
                    if (!incoming) throw new Error('немає #gazu-grid у відповіді');

                    Array.prototype.slice.call(incoming.children).forEach(function (node) {
                        var el = document.importNode(node, true);
                        grid.appendChild(el);
                        // Alpine не ініціалізує сам вузли, додані після старту.
                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(el);
                        }
                    });

                    var nextBox = doc.getElementById('gazu-load-more');
                    box.dataset.next = (nextBox && nextBox.dataset.next) || '';
                    setStatus();

                    if (box.dataset.next) {
                        if (btn) {
                            btn.textContent = 'Показати ще';
                            btn.removeAttribute('aria-busy');
                            btn.setAttribute('href', box.dataset.next);
                        }
                    } else if (btn) {
                        btn.remove();
                        btn = null;
                        if (status) status.textContent = 'Це всі товари за вашим запитом.';
                    }
                })
                .catch(function () {
                    // Мережа впала — повертаємо кнопці робочий стан, посилання живе.
                    if (btn) { btn.textContent = 'Показати ще'; btn.removeAttribute('aria-busy'); }
                    if (status) status.textContent = 'Не вдалося завантажити. Спробуйте ще раз.';
                })
                .finally(function () { busy = false; });
        }

        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                load();
            });
        }

        if (box.dataset.mode === 'infinite' && 'IntersectionObserver' in window) {
            // rootMargin — тягнемо ще до того, як блок реально видно.
            var io = new IntersectionObserver(function (entries) {
                var hit = entries.some(function (en) { return en.isIntersecting; });
                if (!hit) return;
                if (box.dataset.next) load();
                else io.disconnect();
            }, { rootMargin: '400px 0px' });
            io.observe(box);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCatalogLoadMore);
    } else {
        initCatalogLoadMore();
    }
    document.addEventListener('gazu:navigated', initCatalogLoadMore);
    document.addEventListener('livewire:navigated', initCatalogLoadMore);
})();
