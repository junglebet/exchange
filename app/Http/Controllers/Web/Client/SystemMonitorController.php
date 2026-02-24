<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SystemMonitor\SystemMonitorFormRequest;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Setting;

class SystemMonitorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Setting::get('system-monitor.ping', false)) {
            return Redirect::route('home');
        }

        return Inertia::render('Auth/Ping');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(SystemMonitorFormRequest $request)
    {
        Setting::set('system-monitor.ping', $request->get('ping'));

        return Redirect::route('home');
    }
}
