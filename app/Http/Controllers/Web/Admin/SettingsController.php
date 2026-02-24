<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Settings\SettingsFormRequest;
use App\Services\Settings\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Setting;

class SettingsController extends Controller
{
    public function index() {

        $settingsService = new SettingsService();

        return Inertia::render('Admin/Settings/Index', [
            'general' => $settingsService->getSettings('general'),
            'trade' => $settingsService->getSettings('trade'),
            'mail' => $settingsService->getSettings('mail'),
            'coinpayments' => $settingsService->getSettings('coinpayments'),
            'recaptcha' => $settingsService->getSettings('recaptcha'),
            'ethereum' => $settingsService->getSettings('ethereum'),
            'bnb' => $settingsService->getSettings('bnb'),
            'bitcoin' => $settingsService->getSettings('bitcoin'),
            'polygon' => $settingsService->getSettings('polygon'),
            'tron' => $settingsService->getSettings('tron'),
            'stripe' => $settingsService->getSettings('stripe'),
            'notification' => $settingsService->getSettings('notification'),
            'social' => $settingsService->getSettings('social'),
        ]);
    }

    /**
     * Update resource.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(SettingsFormRequest $request)
    {
        $key = array_key_first($request->all());

        (new SettingsService())->updateBatch($key, $request->get($key));

        return Redirect::route('admin.settings');
    }
}
