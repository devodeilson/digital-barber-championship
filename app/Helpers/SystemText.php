<?php

namespace App\Helpers;

use App\Models\SystemText as SystemTextModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

class SystemText
{
    public static function get($key, $default = null)
    {
        $locale = App::getLocale();
        $cacheKey = "system_text.{$key}.{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $locale, $default) {
            $text = SystemTextModel::where('key', $key)->first();

            if (!$text) {
                return $default ?? $key;
            }

            $content = $text->{"content_{$locale}"};
            return $content ?: ($text->content_pt ?: ($default ?? $key));
        });
    }
}
