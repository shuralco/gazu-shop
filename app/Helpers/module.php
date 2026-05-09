<?php

use App\Support\ModuleManager;

if (! function_exists('module')) {
    /**
     * Resolve a module's state. Usage:
     *   module('loyalty')->enabled()
     *   module('loyalty')->name()
     *   if (module('loyalty')->enabled()) { ... }
     */
    function module(string $key): ModuleManager
    {
        return ModuleManager::for($key);
    }
}
