<?php

namespace App\Livewire\User;

use App\Models\UserAddress;
use App\Services\AddressService;
use Livewire\Component;

class AddressBookComponent extends Component
{
    public string $label = '';

    public string $first_name = '';

    public string $last_name = '';

    public string $phone = '';

    public string $city = '';

    public string $address = '';

    public string $postal_code = '';

    public bool $is_default = false;

    public ?int $editingId = null;

    public bool $showForm = false;

    public function save(): void
    {
        $validated = $this->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'is_default' => ['boolean'],
        ], [
            'first_name.required' => "Ім'я обов'язкове",
            'last_name.required' => "Прізвище обов'язкове",
            'phone.required' => "Телефон обов'язковий",
            'city.required' => "Місто обов'язкове",
            'address.required' => "Адреса обов'язкова",
        ]);

        $service = app(AddressService::class);

        if ($this->editingId) {
            $addressModel = UserAddress::where('user_id', auth()->id())
                ->findOrFail($this->editingId);
            $service->update($addressModel, $validated);
            $this->js("toastr.success('Адресу оновлено')");
        } else {
            $service->create(auth()->user(), $validated);
            $this->js("toastr.success('Адресу додано')");
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $addressModel = UserAddress::where('user_id', auth()->id())->findOrFail($id);

        $this->editingId = $addressModel->id;
        $this->label = $addressModel->label ?? '';
        $this->first_name = $addressModel->first_name;
        $this->last_name = $addressModel->last_name;
        $this->phone = $addressModel->phone;
        $this->city = $addressModel->city;
        $this->address = $addressModel->address;
        $this->postal_code = $addressModel->postal_code ?? '';
        $this->is_default = $addressModel->is_default;
        $this->showForm = true;
    }

    public function delete(int $id): void
    {
        $addressModel = UserAddress::where('user_id', auth()->id())->findOrFail($id);
        app(AddressService::class)->delete($addressModel);
        $this->js("toastr.success('Адресу видалено')");
    }

    public function setDefault(int $id): void
    {
        app(AddressService::class)->setDefault(auth()->user(), $id);
        $this->js("toastr.success('Адресу за замовчуванням змінено')");
    }

    public function resetForm(): void
    {
        $this->reset(['label', 'first_name', 'last_name', 'phone', 'city', 'address', 'postal_code', 'is_default', 'editingId', 'showForm']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.user.address-book-component', [
            'title' => 'Адресна книга',
            'addresses' => auth()->user()->addresses()->orderByDesc('is_default')->orderByDesc('created_at')->get(),
        ]);
    }
}
