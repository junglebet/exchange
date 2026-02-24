<?php

namespace App\Services\SystemMonitor;

use Illuminate\Support\Facades\Artisan;
use Setting;

class SystemMonitorService {

    public function getStatus() {

        $monitor = Setting::get('system-monitor');

        $services[] = [
            'order' => 1,
            'title' => 'Web Server',
            'description' => 'Handles web page rendering and overall website performance.',
            'status' => $monitor['web_server'] ?? null,
            'icon' => 'server'
        ];

        $services[] = [
            'order' => 2,
            'title' => 'Database Connection',
            'description' => 'A database connection lets you access database data sources.',
            'status' => $monitor['database'] ?? null,
            'icon' => 'database'
        ];

        $services[] = [
            'order' => 3,
            'title' => 'Website Access',
            'description' => 'Checks if the frontpage publicly accessible.',
            'status' => $monitor['frontpage'] ?? null,
            'icon' => 'globe'
        ];

        $services[] = [
            'order' => 4,
            'title' => 'Encryption',
            'description' => 'Checks if the encryption module enabled.',
            'status' => $monitor['encryption'] ?? null,
            'icon' => 'hdd'
        ];

        $services[] = [
            'order' => 5,
            'title' => 'Cache Service',
            'description' => 'Cache servers act as a repository for website content, database providing users with accelerated access to cached files and data',
            'status' => $monitor['cache'] ?? null,
            'icon' => 'hdd'
        ];

        $services[] = [
            'order' => 6,
            'title' => 'API Service',
            'description' => 'APIs let your users communicate with your system from third-party sources (mobile apps, trading bots etc).',
            'status' => $monitor['api'] ?? null,
            'icon' => 'wifi'
        ];

        $services[] = [
            'order' => 7,
            'title' => 'Artisan Commands',
            'description' => 'System Terminal Commands running from the command-line interface.',
            'status' => $monitor['artisan'] ?? null,
            'icon' => 'terminal'
        ];

        $services[] = [
            'order' => 8,
            'title' => 'Job Processing',
            'description' => 'Queued jobs that may be processed in the background (mail sending, order matching process etc).',
            'status' => $monitor['jobs'] ?? null,
            'icon' => 'tasks'
        ];

        $services[] = [
            'order' => 9,
            'title' => 'Emails',
            'description' => 'Responsible for sending system emails, deposit/withdraw notifications.',
            'status' => $monitor['email'] ?? null,
            'icon' => 'envelope'
        ];

        $services[] = [
            'order' => 10,
            'title' => 'Websockets',
            'description' => 'Real-time market data streaming without reloading the page',
            'status' => $monitor['websocket'] ?? null,
            'icon' => 'sync-alt'
        ];

        $services[] = [
            'order' => 11,
            'title' => 'Stripe API',
            'description' => 'Handles Credit/Debit Card Deposits',
            'status' => $monitor['stripe'] ?? null,
            'icon' => 'credit-card'
        ];

        $services[] = [
            'order' => 12,
            'title' => 'Coinpayments API',
            'description' => 'Cryptocurrency Payment Gateway',
            'status' => $monitor['coinpayments'] ?? null,
            'icon' => 'coins'
        ];

        $services[] = [
            'order' => 13,
            'title' => 'Ethereum API',
            'description' => 'Ethereum/ERC-20 Node Provider',
            'status' => $monitor['ethereum'] ?? null,
            'icon' => 'code-branch'
        ];

        $services[] = [
            'order' => 14,
            'title' => 'Binance Smart Chain API',
            'description' => 'BSC/BEP-20 Node Provider',
            'status' => $monitor['bsc'] ?? null,
            'icon' => 'code-branch'
        ];

        $services[] = [
            'order' => 15,
            'title' => ' Tron API',
            'description' => 'Tron Node Provider',
            'status' => $monitor['tron'] ?? null,
            'icon' => 'code-branch'
        ];

        $services[] = [
            'order' => 16,
            'title' => ' Bitcoin API',
            'description' => 'Bitcoin Node Provider',
            'status' => $monitor['bitcoin'] ?? null,
            'icon' => 'code-branch'
        ];

        return $services;
    }

    public function startTest() {

        Artisan::call('system-monitor:test');

    }
}
