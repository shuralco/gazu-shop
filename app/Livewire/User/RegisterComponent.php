<?php

namespace App\Livewire\User;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class RegisterComponent extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public function save()
    {
        // Check rate limiting before validation
        $key = 'register.'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', 'Too many registration attempts. Please try again in '.$seconds.' seconds.');

            return;
        }

        // Use RegisterRequest for validation
        $registerRequest = new RegisterRequest;
        $validated = $this->validate($registerRequest->rules(), $registerRequest->messages());

        // Record the attempt
        RateLimiter::hit($key, 60);

        try {
            // Hash password before creating user
            $validated['password'] = Hash::make($validated['password']);

            $user = User::query()->create($validated);

            // Clear rate limiter on successful registration
            RateLimiter::clear($key);

            session()->flash('success', 'Registration successful! Please log in with your credentials.');
            $this->redirectRoute('login', ['locale' => app()->getLocale()], navigate: true);
        } catch (\Exception $e) {
            $this->addError('email', 'Registration failed. Please try again.');
            $this->reset('password');
        }
    }

    public function render()
    {
        return view('livewire.user.register-component', [
            'title' => 'Register',
        ]);
    }
}
