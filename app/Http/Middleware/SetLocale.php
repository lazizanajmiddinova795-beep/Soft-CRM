<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $globalLocale = Cache::remember('sys_language', 3600, function () {
                if (class_exists(\App\Models\Setting::class)) {
                    $setting = \App\Models\Setting::where('key', 'sys_language')->first();
                    return $setting ? $setting->value : config('app.locale');
                }
                return config('app.locale');
            });

            $locale = $globalLocale;
            if (auth()->check() && auth()->user()->ui_language) {
                $locale = auth()->user()->ui_language;
            }

            app()->setLocale($locale);
        } catch (\Exception $e) {
            // Fallback
        }

        return $next($request);
    }
}
