<x-filament-panels::page.simple>
    <div class="gazu-auth-card-inner">
        <div class="gazu-auth-mobile-brand">
            <div class="gazu-auth-logo-mark sm">GZ</div>
            <div class="gazu-auth-brand">GAZU admin</div>
        </div>

        <div class="gazu-auth-card-header">
            <h2>Вхід в адмінку</h2>
            <p>Введіть свій email та пароль, щоб продовжити.</p>
        </div>

        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        <div class="gazu-auth-tip">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>Якщо забули пароль — зверніться до головного адміністратора.</span>
        </div>

        <a href="https://lionex.com.ua" target="_blank" rel="nofollow noopener" class="gazu-auth-dev-card">
            Розроблено <strong>LIONEX</strong> · lionex.com.ua
        </a>

        {{-- Left rail with brand splash, anchored to viewport via fixed positioning --}}
        <aside class="gazu-auth-rail" aria-hidden="true">
            <div class="gazu-auth-pattern"></div>
            <div class="gazu-auth-rail-inner">
                <div class="gazu-auth-logo">
                    <div class="gazu-auth-logo-mark">GZ</div>
                    <div>
                        <div class="gazu-auth-brand">GAZU</div>
                        <div class="gazu-auth-brand-sub">admin · console</div>
                    </div>
                </div>

                <div class="gazu-auth-headline">
                    <span class="gazu-auth-tag">🇨🇳 китайські авто</span>
                    <h1>Запчастини, які&nbsp;точно підходять.</h1>
                    <p>Адмінка магазину автозапчастин. Керування каталогом, замовленнями, цінами та доставкою — все в одному місці.</p>
                </div>

                <ul class="gazu-auth-perks">
                    <li><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg> Каталог · бренди · ціни</li>
                    <li><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg> Замовлення Nova Poshta / Ukrposhta</li>
                    <li><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg> Multi-warehouse · TTN · аналітика</li>
                </ul>

                <a href="https://lionex.com.ua" target="_blank" rel="nofollow noopener" class="gazu-auth-dev">
                    <span>Розроблено</span>
                    <img src="{{ asset('lionex-logo.svg') }}" alt="LIONEX" />
                </a>

                <div class="gazu-auth-foot">
                    <span>© {{ date('Y') }} GAZU</span>
                    <a href="{{ url('/') }}" class="gazu-auth-foot-link">← на сайт</a>
                </div>
            </div>
        </aside>
    </div>

    <style>
    :root {
        --gz-ink: #0e1b2c;
        --gz-ink-2: #1c2a3f;
        --gz-blue: #2453ff;
        --gz-paper: #f6f8fc;
        --gz-line: #e6e9f0;
        --gz-graphite: #4a5468;
    }

    /* Reshape Filament's simple layout: full-bleed split (rail | card) */
    body.fi-body { background: linear-gradient(135deg, var(--gz-paper) 0%, #eef1f7 100%); }
    .dark body.fi-body { background: linear-gradient(135deg, #0c121e 0%, #131c2c 100%); }

    .fi-simple-layout { background: transparent !important; }
    .fi-simple-main-ctn { padding-left: 0; padding-right: 0; }
    .fi-simple-main {
        max-width: 480px !important;
        background: #fff !important;
        border-radius: 16px !important;
        box-shadow: 0 1px 2px rgba(14,27,44,0.05), 0 20px 50px -20px rgba(14,27,44,0.18) !important;
        padding: 36px !important;
        margin: 32px auto !important;
        z-index: 2;
        position: relative;
    }
    .dark .fi-simple-main { background: #161f30 !important; box-shadow: 0 1px 2px rgba(0,0,0,0.4), 0 20px 50px -20px rgba(0,0,0,0.55) !important; }
    .fi-simple-page > section > header { display: none; } /* hide default heading */

    @media (max-width: 640px) {
        .fi-simple-main { padding: 28px 22px !important; border-radius: 14px !important; }
    }

    /* On wide screens move card to the right half */
    @media (min-width: 1024px) {
        .fi-simple-main-ctn { justify-content: flex-end !important; padding-right: 7vw; }
        .fi-simple-main { margin-right: 0 !important; max-width: 440px !important; }
    }

    /* ─── Mobile brand strip ─── */
    .gazu-auth-mobile-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; color: var(--gz-ink); }
    @media (min-width: 1024px) { .gazu-auth-mobile-brand { display: none; } }
    .gazu-auth-mobile-brand .gazu-auth-brand { font-size: 18px; }
    .gazu-auth-logo-mark {
        width: 44px; height: 44px; border-radius: 10px;
        background: var(--gz-blue); color: #fff;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 17px; letter-spacing: -0.03em;
        box-shadow: 0 8px 24px -8px rgba(36,83,255,0.55);
    }
    .gazu-auth-logo-mark.sm { width: 36px; height: 36px; font-size: 14px; border-radius: 8px; }
    .gazu-auth-brand { font-size: 22px; font-weight: 700; letter-spacing: -0.02em; line-height: 1; color: #fff; }
    .gazu-auth-brand-sub {
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 11px; color: #9eaccb; letter-spacing: 0.08em; text-transform: uppercase; margin-top: 4px;
    }

    /* ─── Card header ─── */
    .gazu-auth-card-header { margin-bottom: 24px; }
    .gazu-auth-card-header h2 {
        font-size: 26px; font-weight: 700; letter-spacing: -0.02em;
        margin: 0 0 6px; color: var(--gz-ink);
    }
    .dark .gazu-auth-card-header h2 { color: #fff; }
    .gazu-auth-card-header p { font-size: 14px; color: var(--gz-graphite); margin: 0; }
    .dark .gazu-auth-card-header p { color: #9aa7bf; }

    /* Form polish */
    .fi-simple-main .fi-input-wrp:focus-within {
        border-color: var(--gz-blue);
        box-shadow: 0 0 0 3px rgba(36,83,255,0.12);
    }
    .fi-simple-main .fi-btn-color-primary {
        background: var(--gz-ink) !important;
        border-radius: 10px !important;
        padding: 11px 18px !important;
        font-weight: 600 !important;
    }
    .fi-simple-main .fi-btn-color-primary:hover { background: var(--gz-ink-2) !important; }

    .gazu-auth-tip {
        display: flex; gap: 8px; align-items: flex-start; margin-top: 20px;
        padding: 12px 14px; background: var(--gz-paper);
        border: 1px solid var(--gz-line); border-radius: 10px;
        font-size: 12.5px; color: var(--gz-graphite); line-height: 1.45;
    }
    .gazu-auth-tip svg { color: var(--gz-blue); }
    .dark .gazu-auth-tip { background: #1c2538; border-color: #28344e; color: #9aa7bf; }

    /* ─── Left rail (brand splash, fixed) ─── */
    .gazu-auth-rail {
        display: none;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        width: 50%;
        background: linear-gradient(150deg, var(--gz-ink) 0%, #1a2a44 65%, #233655 100%);
        overflow: hidden;
        z-index: 1;
    }
    @media (min-width: 1024px) { .gazu-auth-rail { display: block; } }
    .gazu-auth-pattern {
        position: absolute; inset: 0;
        background-image:
            radial-gradient(circle at 25% 30%, rgba(36,83,255,0.18) 0%, transparent 45%),
            radial-gradient(circle at 78% 70%, rgba(255,255,255,0.04) 0%, transparent 38%),
            repeating-linear-gradient(45deg, rgba(255,255,255,0.025) 0 2px, transparent 2px 24px);
        pointer-events: none;
    }
    .gazu-auth-rail-inner {
        position: relative; z-index: 1;
        height: 100%; padding: 56px 7vw 40px 7vw;
        display: flex; flex-direction: column; justify-content: space-between;
        color: #fff;
    }
    .gazu-auth-logo { display: flex; align-items: center; gap: 14px; }

    .gazu-auth-headline { margin: 56px 0; max-width: 460px; }
    .gazu-auth-tag {
        display: inline-flex; align-items: center; padding: 5px 10px;
        background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14);
        border-radius: 999px;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase;
        color: #d8e0ee; margin-bottom: 22px;
    }
    .gazu-auth-headline h1 {
        font-size: 40px; font-weight: 700; letter-spacing: -0.025em; line-height: 1.08;
        margin: 0 0 18px; color: #fff;
    }
    .gazu-auth-headline p { font-size: 15px; line-height: 1.55; color: #c8d0e1; margin: 0; max-width: 420px; }

    .gazu-auth-perks { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
    .gazu-auth-perks li { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #b8c2d6; }
    .gazu-auth-perks li svg { color: #5ad0a3; flex-shrink: 0; }

    .gazu-auth-foot {
        display: flex; align-items: center; justify-content: space-between;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 11px; letter-spacing: 0.06em; color: #6a7a96; text-transform: uppercase;
    }
    .gazu-auth-foot-link { color: #c8d0e1; text-decoration: none; transition: color 0.15s; }
    .gazu-auth-foot-link:hover { color: #fff; }

    /* ─── «Розроблено LIONEX» — rail (dark, white logo) ─── */
    .gazu-auth-dev {
        display: inline-flex; align-items: center; gap: 9px;
        margin-top: 26px; padding-top: 18px;
        border-top: 1px solid rgba(255,255,255,0.08);
        text-decoration: none;
        font-family: 'JetBrains Mono', ui-monospace, monospace;
        font-size: 10.5px; letter-spacing: 0.08em; text-transform: uppercase;
        color: #6a7a96; transition: color 0.15s, opacity 0.15s;
    }
    .gazu-auth-dev img { height: 19px; width: auto; display: block; opacity: 0.85; transition: opacity 0.15s; }
    .gazu-auth-dev:hover { color: #c8d0e1; }
    .gazu-auth-dev:hover img { opacity: 1; }

    /* ─── «Розроблено LIONEX» — card fallback (light bg, mobile) ─── */
    .gazu-auth-dev-card {
        display: block; margin-top: 18px; text-align: center;
        font-size: 11.5px; letter-spacing: 0.02em;
        color: #98a2b6; text-decoration: none; transition: color 0.15s;
    }
    .gazu-auth-dev-card strong { color: var(--gz-ink); font-weight: 700; }
    .dark .gazu-auth-dev-card strong { color: #fff; }
    .gazu-auth-dev-card:hover { color: var(--gz-blue); }
    .gazu-auth-dev-card:hover strong { color: var(--gz-blue); }
    @media (min-width: 1024px) { .gazu-auth-dev-card { display: none; } } /* rail shows it on desktop */
    </style>
</x-filament-panels::page.simple>
