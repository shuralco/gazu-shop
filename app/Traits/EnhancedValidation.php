<?php

namespace App\Traits;

use Illuminate\Validation\Rule;

trait EnhancedValidation
{
    /**
     * Enhanced validation rules with security improvements
     */
    protected function getEnhancedRules(): array
    {
        return [
            'search' => ['string', 'max:255', 'regex:/^[a-zA-Zа-яА-ЯіІїЇєЄ0-9\s\-_.]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Zа-яА-ЯіІїЇєЄ\s\-\'\.]+$/u'],
            'phone' => ['string', 'max:20', 'regex:/^[\+]?[0-9\(\)\-\s]+$/'],
            'price' => ['numeric', 'min:0', 'max:999999.99'],
            'quantity' => ['integer', 'min:1', 'max:999'],
            'slug' => ['string', 'max:255', 'regex:/^[a-z0-9\-]+$/'],
            'url' => ['url', 'max:500'],
            'image' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'description' => ['string', 'max:5000'],
            'title' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get validation rule for specific field
     */
    protected function getFieldRules(string $field): array
    {
        return $this->getEnhancedRules()[$field] ?? ['string', 'max:255'];
    }

    /**
     * Sanitize input before validation
     */
    protected function sanitizeInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Remove potential XSS
                $input[$key] = strip_tags($value);
                // Normalize whitespace
                $input[$key] = preg_replace('/\s+/', ' ', trim($input[$key]));
            }
        }

        return $input;
    }

    /**
     * Validate with enhanced security
     */
    protected function validateSecure(array $rules, array $data = []): array
    {
        $data = $data ?: $this->all();
        $data = $this->sanitizeInput($data);

        return $this->validate($rules, [], [], $data);
    }

    /**
     * Rate limiting for validation-heavy operations
     */
    protected function rateLimitValidation(string $key, int $maxAttempts = 10): bool
    {
        $attempts = cache()->get("validation_attempts_{$key}", 0);

        if ($attempts >= $maxAttempts) {
            $this->addError('general', 'Забагато спроб. Спробуйте через кілька хвилин.');

            return false;
        }

        cache()->put("validation_attempts_{$key}", $attempts + 1, 300); // 5 minutes

        return true;
    }

    /**
     * Smart validation messages in Ukrainian
     */
    protected function getValidationMessages(): array
    {
        return [
            'required' => 'Поле :attribute є обов\'язковим.',
            'email' => 'Поле :attribute повинно містити правильну email адресу.',
            'min' => 'Поле :attribute повинно містити мінімум :min символів.',
            'max' => 'Поле :attribute не може містити більше :max символів.',
            'numeric' => 'Поле :attribute повинно бути числом.',
            'integer' => 'Поле :attribute повинно бути цілим числом.',
            'regex' => 'Поле :attribute має неправильний формат.',
            'image' => 'Поле :attribute повинно бути зображенням.',
            'mimes' => 'Поле :attribute повинно бути файлом типу: :values.',
            'url' => 'Поле :attribute повинно бути правильним URL.',
        ];
    }
}
