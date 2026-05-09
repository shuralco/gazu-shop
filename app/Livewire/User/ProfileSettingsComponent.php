<?php

namespace App\Livewire\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileSettingsComponent extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public ?string $birthdate = null;

    public $avatar;

    public string $current_password = '';

    public string $new_password = '';

    public bool $email_orders = true;

    public bool $email_promotions = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->birthdate = $user->birthdate?->format('Y-m-d');

        $preferences = $user->notification_preferences ?? [];
        $this->email_orders = $preferences['email_orders'] ?? true;
        $this->email_promotions = $preferences['email_promotions'] ?? false;
    }

    public function save(): void
    {
        $user = User::query()->findOrFail(auth()->id());

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email,' . auth()->id()],
            'phone' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'email_orders' => ['boolean'],
            'email_promotions' => ['boolean'],
        ];

        if ($this->avatar) {
            $rules['avatar'] = ['image', 'max:2048'];
        }

        if (! empty($this->new_password)) {
            $rules['current_password'] = ['required'];
            $rules['new_password'] = [
                'required',
                'string',
                Password::min(8)->letters()->mixedCase()->numbers(),
            ];
        }

        $validated = $this->validate($rules, [
            'name.required' => "Ім'я обов'язкове",
            'email.required' => 'Email обов\'язковий',
            'email.email' => 'Введіть коректний email',
            'email.unique' => 'Цей email вже використовується',
            'current_password.required' => 'Введіть поточний пароль',
            'new_password.required' => 'Введіть новий пароль',
            'birthdate.before' => 'Дата народження має бути в минулому',
        ]);

        // Verify current password if changing
        if (! empty($this->new_password)) {
            if (! Hash::check($this->current_password, $user->password)) {
                $this->addError('current_password', 'Поточний пароль невірний');

                return;
            }
        }

        try {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?: null,
                'birthdate' => $validated['birthdate'] ?: null,
                'notification_preferences' => [
                    'email_orders' => $this->email_orders,
                    'email_promotions' => $this->email_promotions,
                ],
            ];

            if (! empty($this->new_password)) {
                $updateData['password'] = Hash::make($this->new_password);
            }

            if ($this->avatar) {
                if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                    \Storage::disk('public')->delete($user->avatar);
                }
                $updateData['avatar'] = $this->avatar->store('avatars', 'public');
            }

            $user->update($updateData);
            $this->reset(['current_password', 'new_password', 'avatar']);
            $this->js("toastr.success('Профіль оновлено')");
        } catch (\Exception $e) {
            $this->js("toastr.error('Помилка при збереженні')");
        }
    }

    public function render()
    {
        return view('livewire.user.profile-settings-component', [
            'title' => 'Налаштування',
        ]);
    }
}
