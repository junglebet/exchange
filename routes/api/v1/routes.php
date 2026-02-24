<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\CurrencyController;
use App\Http\Controllers\Api\v1\MarketController;
use App\Http\Controllers\Api\v1\OrderController;
use App\Http\Controllers\Api\v1\WalletController;
use App\Http\Controllers\Api\v1\LaunchpadController;
use App\Http\Controllers\Api\v1\StakingController;
use App\Http\Controllers\Api\v1\FuturesController;
use App\Http\Controllers\Api\v1\Gateways\EthereumController;
use App\Http\Controllers\Api\v1\SettingController;
use App\Http\Controllers\Api\v1\Gateways\BscController;
use App\Http\Controllers\Api\v1\Gateways\BitcoinController;
use App\Http\Controllers\Api\v1\Cmc\MarketCmcController;
use App\Http\Controllers\Api\v1\Cmc\MarketCoingeckoController;
use App\Http\Controllers\Api\v1\Gateways\StripeController;
use App\Http\Controllers\Web\Client\ArticleController;
use App\Http\Controllers\Web\Client\MarketController as MarketClientController;
use App\Http\Controllers\Web\Client\WalletController as WalletClientController;
use App\Http\Controllers\Web\Client\LaunchpadController as LaunchpadClientController;
use App\Http\Controllers\Web\Client\StakingController as StakingClientController;
use App\Http\Controllers\Api\v1\Gateways\PerfectMoneyController;
/*
 *
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/servertime', [SettingController::class, 'time'])->name('server-time');

Route::group(['prefix' => 'v1', 'middle', 'middleware' => ['throttle:api']], function () {

    Route::post('auth/token', [SettingController::class, 'auth'])->name('auth.token');

    Route::get('language/file', [SettingController::class, 'languageFile'])->name('language.file');

    Route::get('articles/featured', [ArticleController::class, 'featured'])->name('articles.featured');

    Route::group(['middleware' => ['throttle:time']], function () {
        Route::get('/server-time', [SettingController::class, 'time'])->name('server-time');
    });


    Route::group(['middleware' => ['auth:sanctum', 'maintenance']], function () {

        Route::get('/auth/user', [SettingController::class, 'user'])->name('auth.user');

        Route::post('/orders/cancel', [OrderController::class, 'cancel'])->name('orders.api.cancel');
        Route::get('/orders/open', [OrderController::class, 'open'])->name('orders.api.open');
        Route::post('/orders/futures/cancel', [FuturesController::class, 'cancel'])->name('orders.api.futures.cancel');
        Route::get('/orders/futures/open', [FuturesController::class, 'open'])->name('orders.api.futures.open');

        // Wallet routes
        Route::get('/wallets/getAddress', [WalletController::class, 'getAddress'])->name('wallets.api.getAddress');
        Route::get('/wallets/balance', [WalletClientController::class, 'getBalance'])->name('wallets.api.getBalance');
        Route::get('/wallets/deposit/networks', [WalletController::class, 'loadNetworks'])->name('wallets.api.deposit.networks');

        // Deposit routes
        Route::get('/wallets/deposits/{type}', [WalletController::class, 'getDeposits'])->name('wallets.api.deposits');
        Route::get('/wallets/fiat-deposits/{type?}', [WalletController::class, 'getFiatDeposits'])->name('wallets.api.deposits.fiat');

        Route::get('/wallets/deposit/crypto', [WalletController::class, 'getDepositInfo'])->name('wallets.api.deposit.crypto.info');
        Route::get('/wallets/withdraw/crypto', [WalletController::class, 'getWithdrawInfo'])->name('wallets.api.withdraw.crypto.info');

        // Withdraw routes
        Route::get('/wallets/withdrawals/{type}', [WalletController::class, 'getWithdrawals'])->name('wallets.api.withdrawals');
        Route::post('/wallets/withdraw', [WalletController::class, 'withdraw'])->name('wallets.api.withdraw');
        Route::get('/wallets/fiat-withdrawals', [WalletController::class, 'getFiatWithdrawals'])->name('wallets.api.withdrawals.fiat');

        Route::post('/wallets/payment/stripe/init', [WalletController::class, 'stripePayment'])->name('wallets.api.deposit.fiat.stripe.load');
        Route::post('/wallets/payment/stripe/validate', [WalletController::class, 'stripePaymentValidate'])->name('wallets.api.deposit.fiat.stripe.validate');

        Route::resource('wallets', WalletController::class);

        Route::get('/currencies/rates', [CurrencyController::class, 'rates'])->name('currencies.api.rates');

        // API For Mobile
        Route::get('/wallets/deposit/coin', [WalletController::class, 'depositCrypto'])->name('wallets.api.deposit.coin');
    });

    Route::get('/networks', [WalletController::class, 'getNetworks'])->name('wallets.api.networks');


    Route::get('/markets/exchange_rate', [MarketClientController::class, 'exchangeRate'])->name('markets.api.exchange_rate');
    Route::get('/market/info', [MarketController::class, 'marketInfo'])->name('market.api.info');

    Route::get('/markets/swap/info', [MarketController::class, 'swap'])->name('markets.api.swap');
    Route::get('/markets/ticker', [MarketController::class, 'ticker'])->name('markets.api.ticker');
    Route::get('/markets/trades', [MarketController::class, 'trades'])->name('markets.api.trades');
    Route::get('/markets/candles/{query?}', [MarketController::class, 'candles'])->name('markets.api.candles');
    Route::get('/markets/orderbook', [MarketController::class, 'orderbook'])->name('markets.api.orderbook');
    Route::get('/markets/historical/trades', [MarketController::class, 'historicalTrades'])->name('markets.api.historical.trades');


    Route::resource('currencies', CurrencyController::class);

    Route::resource('markets', MarketController::class);
    Route::resource('orders', OrderController::class);
    
    // Coinmarketcap & CoinGecko Routes
    Route::group(['prefix' => 'spot'], function () {

        // CMC endpoints
        Route::get('/markets', [MarketCmcController::class, 'markets']);
        Route::get('/ticker', [MarketCmcController::class, 'ticker']);
        Route::get('/orderbook', [MarketCmcController::class, 'orderbook']);
        Route::get('/trades', [MarketCmcController::class, 'trades']);
        Route::get('/assets', [MarketCmcController::class, 'assets']);

        // CoinGecko endpoints
        Route::get('/cg/pairs', [MarketCoingeckoController::class, 'pairs']);
        Route::get('/cg/tickers', [MarketCoingeckoController::class, 'tickers']);
        Route::get('/cg/orderbook', [MarketCoingeckoController::class, 'orderbook']);
        Route::get('/cg/historical_trades', [MarketCoingeckoController::class, 'historicalTrades']);
    });

    // Stripe
    Route::post('/gateways/stripe', [StripeController::class, 'ipn'])->name('stripe.ipn');
});
