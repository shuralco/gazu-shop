<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can create/update products
        return auth()->check() && auth()->user()->is_admin ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? $this->route('product')->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:products,title'.($productId ? ','.$productId : ''),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]+$/',
                'unique:products,slug'.($productId ? ','.$productId : ''),
            ],
            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'short_description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'old_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999.99',
                'gte:price',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:0',
                'max:99999',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
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
            'title.required' => 'Product title is required.',
            'title.unique' => 'A product with this title already exists.',
            'slug.required' => 'Product slug is required.',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'A product with this slug already exists.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'old_price.gte' => 'Old price must be greater than or equal to current price.',
            'quantity.required' => 'Product quantity is required.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category does not exist.',
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
            'title' => 'product title',
            'slug' => 'product slug',
            'price' => 'product price',
            'old_price' => 'old price',
            'quantity' => 'stock quantity',
            'category_id' => 'category',
            'is_active' => 'active status',
        ];
    }
}
