<?php

namespace App\Support\Modules;

/**
 * Validates module settings against extended settings_schema.
 *
 * Schema fields (all optional except type):
 *   - type: 'string' | 'int' | 'bool' | 'float'
 *   - default: any (used if value missing)
 *   - required: bool
 *   - min: int|float (numeric range)
 *   - max: int|float
 *   - pattern: regex (string only)
 *   - enum: list of allowed values
 *   - label: human-readable name
 *   - help: help text
 *
 * Example manifest field:
 *   "settings_schema": {
 *     "default_rate": { "type": "int", "default": 1, "min": 1, "max": 100, "required": true,
 *                       "label": "Базова ставка нарахування" },
 *     "currency": { "type": "string", "enum": ["UAH", "USD", "EUR"], "default": "UAH" }
 *   }
 */
class ModuleSettingsValidator
{
    /**
     * @return array{values: array<string,mixed>, errors: array<string,string>}
     */
    public static function validate(array $values, array $schema): array
    {
        $errors = [];
        $clean = [];

        foreach ($schema as $key => $def) {
            $raw = $values[$key] ?? null;
            $type = $def['type'] ?? 'string';

            // Apply default if empty + has default
            if (($raw === null || $raw === '') && isset($def['default'])) {
                $clean[$key] = $def['default'];

                continue;
            }

            // Required check
            if (($def['required'] ?? false) && ($raw === null || $raw === '')) {
                $errors[$key] = ($def['label'] ?? $key).' — обов\'язкове поле';

                continue;
            }

            // Skip if empty and not required (no default)
            if ($raw === null || $raw === '') {
                $clean[$key] = null;

                continue;
            }

            // Type coercion + validation
            switch ($type) {
                case 'int':
                    if (! is_numeric($raw)) {
                        $errors[$key] = ($def['label'] ?? $key).' — має бути числом';
                        break;
                    }
                    $val = (int) $raw;
                    if (isset($def['min']) && $val < $def['min']) {
                        $errors[$key] = ($def['label'] ?? $key)." — мінімум {$def['min']}";
                        break;
                    }
                    if (isset($def['max']) && $val > $def['max']) {
                        $errors[$key] = ($def['label'] ?? $key)." — максимум {$def['max']}";
                        break;
                    }
                    $clean[$key] = $val;
                    break;

                case 'float':
                    if (! is_numeric($raw)) {
                        $errors[$key] = ($def['label'] ?? $key).' — має бути числом';
                        break;
                    }
                    $val = (float) $raw;
                    if (isset($def['min']) && $val < $def['min']) {
                        $errors[$key] = ($def['label'] ?? $key)." — мінімум {$def['min']}";
                        break;
                    }
                    if (isset($def['max']) && $val > $def['max']) {
                        $errors[$key] = ($def['label'] ?? $key)." — максимум {$def['max']}";
                        break;
                    }
                    $clean[$key] = $val;
                    break;

                case 'bool':
                    $clean[$key] = filter_var($raw, FILTER_VALIDATE_BOOLEAN);
                    break;

                case 'string':
                default:
                    $val = (string) $raw;
                    if (isset($def['min']) && mb_strlen($val) < $def['min']) {
                        $errors[$key] = ($def['label'] ?? $key)." — мінімум {$def['min']} символів";
                        break;
                    }
                    if (isset($def['max']) && mb_strlen($val) > $def['max']) {
                        $errors[$key] = ($def['label'] ?? $key)." — максимум {$def['max']} символів";
                        break;
                    }
                    if (! empty($def['pattern']) && ! preg_match('/'.$def['pattern'].'/u', $val)) {
                        $errors[$key] = ($def['label'] ?? $key).' — невірний формат';
                        break;
                    }
                    if (! empty($def['enum']) && ! in_array($val, $def['enum'], true)) {
                        $errors[$key] = ($def['label'] ?? $key).' — дозволено: '.implode(', ', $def['enum']);
                        break;
                    }
                    $clean[$key] = $val;
                    break;
            }
        }

        return ['values' => $clean, 'errors' => $errors];
    }
}
