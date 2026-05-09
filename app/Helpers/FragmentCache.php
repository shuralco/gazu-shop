<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class FragmentCache
{
    public static function remember(string $key, $tags, int $ttl, callable $callback): string
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        return Cache::tags($tags)->remember($key, $ttl, function () use ($callback) {
            return $callback();
        });
    }

    public static function renderCached(string $key, $model, string $view, array $data = [], int $ttl = 3600): string
    {
        $cacheKey = $key.'_'.$model->id.'_'.$model->updated_at->timestamp;
        $tags = [class_basename($model), $model->getTable()];

        return self::remember($cacheKey, $tags, $ttl, function () use ($view, $data, $model) {
            return view($view, array_merge($data, compact('model')))->render();
        });
    }
}
