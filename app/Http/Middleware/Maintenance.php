<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Setting;

class Maintenance
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
        $maintenance = Setting::get('general.maintenance_status', false);

        $adminDashboardPrefix = '/exchange-control-panel';
        $adminDashboardLoginRoute = 'admin.login';

        if($request->route()->getPrefix() == $adminDashboardPrefix && $request->route()->getName() != $adminDashboardLoginRoute && !$request->user()) {
            return Redirect::route($adminDashboardLoginRoute);
        }

        if($maintenance && $request->user() && $request->user()->hasRole('admin')) {
            return $next($request);
        }

        if($maintenance && $request->route()->getPrefix() != $adminDashboardPrefix) {

            if(in_array('api', $request->route()->action['middleware'])) {
                return response()->json(['message' => 'Unfortunately the site is down for a maintenance right now']);
            }

            return Redirect::route('page.maintenance');
        }

        return $next($request);
    }
}
