<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Currency\CurrencyCollection;
use App\Models\Market\Market;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Market\MarketRepository;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource for Currency API.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencyRepository = new CurrencyRepository();

        return new CurrencyCollection($currencyRepository->all(false));
    }

    public function rates() {

        $currencyRepository = new CurrencyRepository();
        $usdt = $currencyRepository->getCurrencyBySymbol('USDT');
        $usd = $currencyRepository->getCurrencyBySymbol('USD');
        $btc = $currencyRepository->getCurrencyBySymbol('BTC');

        $marketRepository = new MarketRepository();
        $walletRepository = new WalletRepository();
        $user = auth()->user();

        $wallets = false;

        if($user) {
            $wallets = $walletRepository->getWallets($user->id);
        }

        $markets = $marketRepository->all(false, false)->pluck('name', 'id');

        $markets = $markets->map(function($market, $key){
            $sanitizedMarket = market_sanitize($market);
            return [$key] = $sanitizedMarket;
        });

        $flippedMarket = $markets->flip()->toArray();

        $currencies = $currencyRepository->all(false, false)->map(function($currency) use ($currencyRepository, $usdt, $usd, $flippedMarket) {

            $rateMarket = $flippedMarket[$currency->symbol . 'USDT'] ?? false;

            if(!$rateMarket) {
                $rateMarket = $flippedMarket[$currency->symbol . 'USD'] ?? false;
            }

            return [
                'id' => $currency->id,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'rate' => $currencyRepository->currencyPriceInUsd($currency, $usdt, $usd, $rateMarket)
            ];
        });

        $totalBalance = 0;

        if($wallets) {

            $totalBalance = $wallets->sum(function ($wallet) use ($currencies) {

                $currency = $currencies->filter(function($c) use ($wallet) {
                    return $c['id'] == $wallet->currency_id;
                })->first();

                return math_multiply(math_sum($wallet->balance_in_wallet, $wallet->balance_in_order), math_formatter($currency['rate'], 18, '.', ''));
            });
        }

        $btcMarket = false;
        $btcPrice =0 ;

        if($btc && $usdt) {
            $btcMarket = Market::where('base_currency_id', $btc->id)->where('quote_currency_id', $usdt->id)->pluck('id');
        } elseif($btc && $usd) {
            $btcMarket = Market::where('base_currency_id', $btc->id)->where('quote_currency_id', $usdt->id)->pluck('id');
        }

        if(isset($btcMarket[0])) {
            $btcPrice = market_get_stats($btcMarket[0], 'last');
        }

        $response = [
            'rates' => $currencies
        ];

        if($user) {
            $response['totatUsdBalance'] = math_formatter($totalBalance, 2);
            $response['totalBtcBalance'] = $btcPrice > 0 ? math_formatter(math_divide($totalBalance, $btcPrice), 8) : 0;
        }

        return response()->json($response);
    }
}
