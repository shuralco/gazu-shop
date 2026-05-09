<?php

namespace App\Livewire\Synthesizers;

use Illuminate\Database\Eloquent\Model;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Spatie\Translatable\HasTranslations;

/**
 * Custom Livewire Synthesizer for Eloquent models using HasTranslations.
 *
 * Problem: Livewire 3 dehydration breaks after first update because
 * HasTranslations stores attributes as JSON arrays internally,
 * which corrupts the Livewire snapshot.
 *
 * Solution: This synthesizer dehydrates translatable models by ID only,
 * and hydrates them fresh from DB. Translatable attributes are resolved
 * to current locale strings during dehydration.
 *
 * @see https://livewire.laravel.com/docs/4.x/synthesizers
 * @see https://github.com/livewire/livewire/discussions/7589
 */
class TranslatableModelSynth extends Synth
{
    public static $key = 'trmdl';

    public static function match($target): bool
    {
        return $target instanceof Model
            && in_array(HasTranslations::class, class_uses_recursive($target));
    }

    public function dehydrate($target): array
    {
        return [
            [
                'class' => get_class($target),
                'id' => $target->getKey(),
            ],
            [],
        ];
    }

    public function hydrate($value)
    {
        $class = $value['class'];
        $id = $value['id'];

        if (! $id || ! class_exists($class)) {
            return null;
        }

        return $class::find($id);
    }

    public function get(&$target, $key)
    {
        return $target->{$key};
    }

    public function set(&$target, $key, $value)
    {
        $target->{$key} = $value;
    }
}
