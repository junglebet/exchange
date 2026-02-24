<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemMonitor\SystemMonitorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Setting;

class SystemMonitorController extends Controller
{
    public function test() {

        $service = new SystemMonitorService();

        $service->startTest();

        return response()->json(['success' => true]);
    }

    public function websocket(Request $request){

        $status = $request->get('status', false);

        $state = "offline";

        if($status) {
            $state = "online";
        }

        Setting::set('system-monitor.websocket', $state);

        return response()->json(['success' => true]);
    }
}
