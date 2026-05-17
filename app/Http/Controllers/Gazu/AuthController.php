<?php

namespace App\Http\Controllers\Gazu;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Простий вхід/реєстрація для GAZU storefront.
 * Використовує дефолтний 'web' guard, той самий, що і чинний /uk кабінет.
 */
class AuthController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('gazu.account');
        }
        return view('gazu.account.auth', ['activeNav' => null]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Невірний email або пароль'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('gazu.account'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:80',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Welcome email — auth.welcome template (editable у /admin/email-templates).
        // Silent fail — реєстрація не повинна ламатись через mail issue.
        try {
            \Mail::to($user->email)->queue(new \App\Mail\TemplatedMail('auth.welcome', [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'site_url' => url('/'),
            ]));
        } catch (\Throwable $e) {
            \Log::warning('Welcome email failed for user #'.$user->id.': '.$e->getMessage());
        }

        return redirect()->route('gazu.account')->with('flash_message', 'Вітаємо у GAZU, '.$user->name.'!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('gazu.home');
    }
}
