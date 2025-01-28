<?php

use App\Models\SystemText;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

if (!function_exists('__t')) {
    function __t($key, $default = null)
    {
        $locale = App::getLocale();
        $cacheKey = "system_text.{$key}.{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $locale, $default) {
            $text = SystemText::where('key', $key)->first();

            if (!$text) {
                return $default ?? $key;
            }

            $content = $text->{"content_{$locale}"};
            return $content ?: ($text->content_pt ?: ($default ?? $key));
        });
    }
}
