<?php

namespace App\Traits;

/**
 * Ensures translatable fields return the current locale string
 * in toArray() / JSON serialization instead of the full translations array.
 *
 * Only overrides attributesToArray() — NOT getAttributes() to avoid
 * breaking spatie's save/update logic which needs the raw JSON.
 */
trait TranslatableToArray
{
    public function attributesToArray(): array
    {
        $array = parent::attributesToArray();

        foreach ($this->getTranslatableAttributes() as $field) {
            if (isset($array[$field]) && is_array($array[$field])) {
                $array[$field] = $this->getTranslation($field, app()->getLocale());
            }
        }

        return $array;
    }
}
