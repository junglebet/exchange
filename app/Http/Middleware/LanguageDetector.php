<?php

namespace App\Http\Middleware;

use App\Repositories\Language\LanguageRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Setting;

class LanguageDetector
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        /*
         * Set App Locale
         */

        $user = request()->user();

        if($user && $user->language && $user->language->status) {
            $lang = $user->language->slug;

        } else {

            $langFromCookie =  Cookie::get('user_language');

            if($langFromCookie) {
                $defaultLanguage = (new LanguageRepository())->getBySlug($langFromCookie, true);
            } else {
                $defaultLanguage = (new LanguageRepository())->getByDefault();
            }

            if ($defaultLanguage) {
                $lang = $defaultLanguage->slug;
            } else {
                $lang = config('app.fallback_locale');
            }
        }

        App::setLocale($lang);

        return $next($request);
    }
}
