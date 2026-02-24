<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Report\ReportRepository;
use App\Services\SystemMonitor\SystemMonitorService;
use Inertia\Inertia;
use Setting;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stats = (new ReportRepository())->getDashboardStats();

        $service = new SystemMonitorService();

        return Inertia::render('Admin/Dashboard',[
            'stats' => $stats,
            'services' => $service->getStatus(),
            'last' => Setting::get('system-monitor.last_checked', '')
        ]);
    }

    /**
     * Login page
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        return Inertia::render('Auth/LoginAdmin');
    }
}
