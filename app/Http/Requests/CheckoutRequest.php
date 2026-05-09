<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
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
            ],
            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required for the order.',
            'name.regex' => 'Full name can only contain letters, spaces, hyphens, apostrophes and dots.',
            'email.required' => 'Email address is required for order confirmation.',
            'email.email' => 'Please enter a valid email address.',
            'note.max' => 'Order note cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'note' => 'order note',
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                // Check if cart is not empty
                $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
                if ($cartTotal <= 0) {
                    $validator->errors()->add('cart', 'Your cart is empty. Please add items before checkout.');
                }

                // Validate name length
                if ($this->name && strlen(trim($this->name)) < 2) {
                    $validator->errors()->add('name', 'Full name must be at least 2 characters long.');
                }

                // Check if note contains potentially harmful content
                if ($this->note && preg_match('/<script|javascript:|data:/i', $this->note)) {
                    $validator->errors()->add('note', 'Order note contains invalid content.');
                }
            },
        ];
    }
}
