<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    function setting($key)
    {
        return Setting::where('key', $key)->value('value');
    }
}