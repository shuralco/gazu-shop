<?php

namespace App\Livewire\User;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class LoginComponent extends Component
{
    public string $email = '';

    public string $password = '';

    public function login()
    {
        // Check rate limiting before validation
        $key = 'login.'.request()->ip().'|'.$this->email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', 'Too many login attempts. Please try again in '.$seconds.' seconds.');

            return;
        }

        // Use LoginRequest for validation
        $loginRequest = new LoginRequest;
        $validated = $this->validate($loginRequest->rules(), $loginRequest->messages());

        // Record the attempt
        RateLimiter::hit($key, 60);

        if (Auth::attempt($validated)) {
            // Clear rate limiter on successful login
            RateLimiter::clear($key);

            session()->flash('success', 'Login successful');
            $this->redirectRoute('account', ['locale' => app()->getLocale()], navigate: true);
        } else {
            $this->addError('email', 'These credentials do not match our records.');
            $this->reset('password');
        }
    }

    public function render()
    {
        return view('livewire.user.login-component', [
            'title' => 'Login',
        ]);
    }
}
