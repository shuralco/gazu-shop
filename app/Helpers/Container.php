<?php

namespace App\Helpers;

class Container
{
    private static array $container = [];

    public static function set(string $key, $value): void
    {
        self::$container[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return self::$container[$key] ?? $default;
    }

    public static function clear(?string $key = null): void
    {
        if ($key) {
            unset(self::$container[$key]);
        } else {
            self::$container = [];
        }
    }
}
