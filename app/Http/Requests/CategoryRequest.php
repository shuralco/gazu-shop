<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can create/update categories
        return auth()->check() && auth()->user()->is_admin ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category') ? $this->route('category')->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:categories,title'.($categoryId ? ','.$categoryId : ''),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                'unique:categories,slug'.($categoryId ? ','.$categoryId : ''),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'boolean',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Category title is required.',
            'title.unique' => 'A category with this title already exists.',
            'slug.required' => 'Category slug is required.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'A category with this slug already exists.',
            'image.image' => 'File must be an image.',
            'image.mimes' => 'Image must be jpeg, jpg, png, or webp format.',
            'image.max' => 'Image size cannot exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'category title',
            'slug' => 'category slug',
            'description' => 'category description',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                // Check if slug is safe (no reserved words)
                $reservedSlugs = ['admin', 'api', 'login', 'register', 'cart', 'checkout', 'search'];
                if (in_array($this->slug, $reservedSlugs)) {
                    $validator->errors()->add('slug', 'This slug is reserved and cannot be used.');
                }
            },
        ];
    }
}
