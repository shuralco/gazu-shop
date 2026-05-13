<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

/**
 * Custom GAZU-branded admin login screen.
 *
 * Extends Filament's default Login page but uses a custom Blade view so we
 * can render a brand splash (gazu logo + tagline) next to the credentials
 * form on wide screens, and keep a clean single-column layout on mobile.
 */
class Login extends BaseLogin
{
    /** @var view-string */
    protected static string $view = 'filament.pages.auth.login';
}
