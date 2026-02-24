<?php

namespace App\Http\Middleware;

use App\Services\Language\LanguageService;
use App\Services\Settings\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Middleware;
use Setting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        $darkModeEnabled = (bool)setting('general.dark_mode_status', false);

        return array_merge(parent::share($request), [
            'jetstream' => null,
            'session' => $request->session()->get('flash', null),
            'siteLogo' => setting('general.logo'),
            'siteName' => config('app.name'),
            'v' => intval(config('app.plan')),
            'social' => setting('social'),
            'languages' => (new LanguageService())->getLanguages(),
            'captcha' => (new SettingsService())->getCaptchaStatus(),
            'captcha_key' => config('captcha.sitekey'),
            'alt' => is_mobile_instance(),
            'isHome' => request()->route()->getName() == 'home',
            'isMarket' => request()->route()->getName() == 'market' || request()->route()->getName() == 'market.lite',
            'theme' => Cookie::get('theme', $darkModeEnabled ? 'dark' : 'light'),
            'theme_mode_enabled' => $darkModeEnabled,
            'referral_code' => request()->get('referral', false),
            'mode' => '',
            'user' => function () use ($request) {

                if (! $request->user()) {
                    return;
                }

                $user = [
                    'id' => $request->user()->id,
                    'email' => $request->user()->email,
                    'referral_code' => $request->user()->referral_code,
                    'kyc_verified' => $request->user()->kyc_verified,
                    'bank_verified' => $request->user()->bank_verified,
                ];

                if($request->user()->hasRole('admin')) {
                    $user['admin'] = true;
                }

                $user['roles'] = array_column($request->user()->roles->toArray(), 'name');

                return array_merge($user, [
                    'two_factor_enabled' => ! is_null($request->user()->two_factor_secret),
                ]);
            },
        ]);
    }
}
