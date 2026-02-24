<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit\FiatDeposit;
use App\Models\Market\Market;
use App\Models\Withdrawal\FiatWithdrawal;
use App\Models\Withdrawal\Withdrawal;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Deposit\DepositRepository;
use App\Repositories\Deposit\FiatDepositRepository;
use App\Repositories\Transaction\ReferralTransactionRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\Wallet\WalletRepository;
use App\Repositories\Withdrawal\FiatWithdrawalRepository;
use App\Repositories\Withdrawal\WithdrawalRepository;
use App\Services\Market\MarketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Setting;

class LiquidityController extends Controller
{
    public function index() {

        $marketService = new MarketService();

        return Inertia::render('Admin/Liquidity/Index', [
            'liquidity' => Cache::get('markets_liquidity.active', []),
            'markets' => $marketService->getMarkets(true, true),
        ]);
    }

    public function run(Request $request) {

        $market = $request->get('market');

        $markets = Cache::get('markets_liquidity.active', []);

        if(isset($markets[$market])) {
            return response()->json([
                'status' => 'Market Liquidity is already running on this market',
                'markets' => Cache::get('markets_liquidity.active', []),
            ]);
        }

        $markets[$market] = true;

        Cache::put('markets_liquidity.active', $markets);

        $response = Http::get('https://api.binance.com/api/v3/ticker/24hr', [
            'symbol' => market_sanitize($market)
        ]);

        if ($response->successful()) {

            $ticker = $response->json();

            $marketModel = Market::whereName($market)->first();

            if($marketModel) {
                $ratio = 0;//$marketModel->discount * 0.01;
                $marketModel->last = $ticker['prevClosePrice'] + ($ticker['prevClosePrice'] * $ratio);
                $marketModel->update();
            }
        }

        //exec("php ".base_path()."/artisan market:liquidity $market > /dev/null & ");

        return response()->json([
            'status' => 'Market Liquidity will be started soon if the market exists and active on Binance',
            'markets' => Cache::get('markets_liquidity.active', []),
        ]);
    }

    public function stop(Request $request) {

        $market = $request->get('market');

        $markets = Cache::get('markets_liquidity.active', []);

        if(isset($markets[$market])) {

            unset($markets[$market]);

            Cache::put('markets_liquidity.active', $markets);

            Cache::forget("markets_liquidity.$market.bids");
            Cache::forget("markets_liquidity.$market.bids_total");

            Cache::forget("markets_liquidity.$market.asks");
            Cache::forget("markets_liquidity.$market.asks_total");
        }

        return response()->json([
            'status' => 'Market Liquidity was stopped',
            'markets' => $markets,
        ]);
    }
}
