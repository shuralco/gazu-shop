<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChangeAccountComponent extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $current_password = '';

    public $avatar;

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function save()
    {
        $user = User::query()->findOrFail(auth()->id());

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s\-\'\.]+$/',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email,'.auth()->id(),
            ],
        ];

        // Add avatar validation if uploaded
        if ($this->avatar) {
            $rules['avatar'] = ['image', 'max:2048'];
        }

        // If password is provided, validate it and require current password
        if (! empty($this->password)) {
            $rules['current_password'] = ['required'];
            $rules['password'] = [
                'required',
                'string',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ];
        }

        $validated = $this->validate($rules, [
            'name.required' => 'Name field is required.',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes and dots.',
            'email.required' => 'Email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already in use.',
            'current_password.required' => 'Current password is required to change password.',
            'password.required' => 'New password is required.',
        ]);

        // Verify current password if new password is provided
        if (! empty($this->password)) {
            if (! Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Current password is incorrect.');

                return;
            }
            $validated['password'] = Hash::make($validated['password']);
            unset($validated['current_password']);
        } else {
            unset($validated['password']);
            unset($validated['current_password']);
        }

        try {
            // Handle avatar upload if provided
            if ($this->avatar) {
                // Delete old avatar if exists
                if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                    \Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar
                $avatarPath = $this->avatar->store('avatars', 'public');
                $validated['avatar'] = $avatarPath;
            }

            $user->update($validated);
            $this->js("toastr.success('Account details updated successfully!')");

            // Clear password and avatar fields after successful update
            $this->reset(['password', 'current_password', 'avatar']);
        } catch (\Exception $e) {
            $this->js("toastr.error('Failed to update account details.')");
        }
    }

    public function render()
    {
        return view('livewire.user.change-account-component', [
            'title' => 'Edit account',
        ]);
    }
}
