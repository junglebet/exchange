<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Web\Client as Client;
use App\Http\Controllers\Web\Admin as Admin;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require_once __DIR__ . '/jetstream.php';

Route::group(['middleware' => ['read.only']], function () {

    Route::get('license', [Client\SystemMonitorController::class, 'index'])->name('system-monitor.ping');
    Route::post('license', [Client\SystemMonitorController::class, 'register'])->name('system-monitor.ping.register');

    /*
    * Maintenance Static Pages
    */
    Route::get('maintenance', [Client\PageController::class, 'maintenance'])->name('page.maintenance');

    Route::group(['middleware' => ['language.detect', 'maintenance', 'ping']], function () {

        Route::get('/', [Client\InitController::class, 'index'])->name('home');

        Route::get('markets/lite', [Client\MarketController::class, 'indexLite'])->name('markets.lite');
        Route::get('market/lite/{market:name}', [Client\MarketController::class, 'showLite'])->name('market.lite');

        /*
         * Public Market Pages
         */
        Route::get('markets', [Client\MarketController::class, 'index'])->name('markets');
        Route::get('markets/exchange_rate', [Client\MarketController::class, 'exchangeRate'])->name('market.exchange_rate');
        Route::get('market/{market:name}', [Client\MarketController::class, 'show'])->name('market');
        Route::get('futures-market/{market:name}', [Client\MarketController::class, 'futuresShow'])->name('futures-market');

        /*
         * Swap
         */
        Route::get('swap', [Client\MarketController::class, 'swap'])->name('swap');

        /*
         * Public Launchpad Pages
         */
        Route::get('launchpads/{sort?}', [Client\LaunchpadController::class, 'index'])->name('launchpads');
        Route::get('launchpad/{launchpad}', [Client\LaunchpadController::class, 'show'])->name('launchpad');
        Route::post('launchpad/submit', [Client\LaunchpadController::class, 'submit'])->name('launchpad.submit');

        /*
         * Public Article Pages
         */
        Route::get('articles/{sort?}', [Client\ArticleController::class, 'index'])->name('articles');
        Route::get('article/short/{article}', [Client\ArticleController::class, 'showShort'])->name('article.short');
        Route::get('article/{article}', [Client\ArticleController::class, 'show'])->name('article');

        /*
         * Public Staking Pages
         */
        Route::get('staking/{sort?}', [Client\StakingController::class, 'index'])->name('stakings');
        Route::get('staking-page/{staking}', [Client\StakingController::class, 'show'])->name('staking');
        Route::post('staking/submit', [Client\StakingController::class, 'submit'])->name('staking.submit');
        Route::post('staking/redeem', [Client\StakingController::class, 'redeem'])->name('staking.redeem');
        Route::post('staking/redemption/calculate', [Client\StakingController::class, 'redemptionCalculate'])->name('staking.redemption.calculate');


        /*
         * Public Chart Page
         */
        Route::group(['middleware' => ['throttle:chart']], function () {
            Route::get('tradingview-chart/config', [Client\ChartController::class, 'config'])->name('chart.config');
            Route::get('tradingview-chart/symbols', [Client\ChartController::class, 'symbols'])->name('chart.symbols');
            Route::get('tradingview-chart/chart-mobile/{symbol}/{route}/{theme}', [Client\ChartController::class, 'mobile'])->name('chart.mobile');
            Route::get('tradingview-chart/chart/{symbol}/{route}', [Client\ChartController::class, 'index'])->name('chart');
            Route::get('tradingview-chart/time', [Client\ChartController::class, 'time'])->name('chart.time');
            Route::get('tradingview-chart/history', [Client\ChartController::class, 'history'])->name('chart.history');
            Route::get('tradingview-chart', [Client\ChartController::class, 'candles'])->name('chart.candles');
        });

        /*
         * Settings Controller
         */
        Route::get('settings/mode', [Client\SettingsController::class, 'mode'])->name('settings.theme.mode');

        /*
         * Language Loader
         */
        Route::get('language/load', [Client\LanguageController::class, 'index'])->name('language.load');
        Route::get('language/set', [Client\LanguageController::class, 'set'])->name('language.set');

        Route::group(['middleware' => ['verified']], function () {

            Route::get('user/kyc', [Client\KycDocumentController::class, 'index'])->name('user.kyc');
            Route::post('user/kyc', [Client\KycDocumentController::class, 'store'])->name('user.kyc.store');

            Route::group(['middleware' => ['throttle:upload']], function () {
                Route::post('/upload', [Client\FileController::class, 'upload'])->name('user-file-upload');
                Route::delete('/upload', [Client\FileController::class, 'delete'])->name('user-file-delete');
            });

            /*
             * Bank Account
            */
            Route::post('/bank-account/plaid-token', [Client\BankAccountController::class, 'plaid_token'])->name('bank_account.plaid_token');
            Route::post('/bank-account/kyc', [Client\BankAccountController::class, 'kyc'])->name('bank_account.kyc_submit');
            Route::post('/bank-account/link-account', [Client\BankAccountController::class, 'link_account'])->name('bank_account.link_account');
            Route::post('/bank-account/delete-account', [Client\BankAccountController::class, 'delete_account'])->name('bank_account.delete_account');
            Route::post('/bank-account/deposit', [Client\BankAccountController::class, 'issueSila'])->name('bank_account.deposit');
            Route::get('/bank-account/accounts', [Client\BankAccountController::class, 'getAccounts'])->name('bank_account.accounts');
            Route::get('/bank-account/transactions', [Client\BankAccountController::class, 'getTransactions'])->name('bank_account.transactions');

            /*
             * Bank Account
            */
            Route::post('/voucher/redeem', [Client\VoucherController::class, 'redeem'])->name('voucher.redeem');
            Route::get('/voucher/transactions', [Client\VoucherController::class, 'transactions'])->name('voucher.transactions');

            // Wallets Page

            Route::get('wallets/lite', [Client\WalletController::class, 'indexLite'])->name('wallets.lite');

            Route::get('wallets', [Client\WalletController::class, 'index'])->name('wallets');

            Route::get('wallets/balance', [Client\WalletController::class, 'getBalance'])->name('wallets.getBalance');

            Route::get('wallets/deposit/crypto/{symbol}', [Client\WalletController::class, 'depositCrypto'])->name('wallets.deposit.crypto');

            Route::get('wallets/deposit/fiat/success', [Client\WalletController::class, 'depositFiatSuccess'])->name('wallets.deposit.fiat.success');
            Route::get('wallets/deposit/fiat/failed', [Client\WalletController::class, 'depositFiatFailed'])->name('wallets.deposit.fiat.failed');

            Route::get('wallets/deposit/fiat/cancel/{symbol}', [Client\WalletController::class, 'depositFiatCancel'])->name('wallets.deposit.fiat.cancel');
            Route::get('wallets/deposit/fiat/{symbol}', [Client\WalletController::class, 'depositFiat'])->name('wallets.deposit.fiat');

            Route::get('wallets/withdraw/crypto/success', [Client\WalletController::class, 'withdrawCryptoSuccess'])->name('wallets.withdraw.crypto.success');
            Route::get('wallets/withdraw/crypto/{symbol}', [Client\WalletController::class, 'withdrawCrypto'])->name('wallets.withdraw.crypto');

            Route::get('wallets/withdraw/fiat/success', [Client\WalletController::class, 'withdrawFiatSuccess'])->name('wallets.withdraw.fiat.success');
            Route::get('wallets/withdraw/fiat/{symbol}', [Client\WalletController::class, 'withdrawFiat'])->name('wallets.withdraw.fiat');

            Route::post('wallets/deposit/store/fiat', [Client\WalletController::class, 'depositStore'])->name('wallets.deposit.store.bank');
            Route::post('wallets/withdraw/store/fiat', [Client\WalletController::class, 'withdrawStore'])->name('wallets.withdraw.store.fiat');

            // Orders Page
            Route::get('orders', [Client\OrderController::class, 'index'])->name('orders');

            // P2P Trade
            Route::get('p2p-trading/create', [Client\PeerTradeController::class, 'create'])->name('p2p.create');

            // Reports Page
            Route::get('reports/deposits', [Client\ReportController::class, 'deposits'])->name('reports.deposits');
            Route::get('reports/fiat-deposits', [Client\ReportController::class, 'fiatDeposits'])->name('reports.deposits.fiat');

            Route::get('reports/withdrawals', [Client\ReportController::class, 'withdrawals'])->name('reports.withdrawals');
            Route::get('reports/fiat-withdrawals', [Client\ReportController::class, 'fiatWithdrawals'])->name('reports.withdrawals.fiat');

            Route::get('reports/trades', [Client\ReportController::class, 'trades'])->name('reports.trades');

            Route::get('orders/order-history', [Client\ReportController::class, 'orderHistory'])->name('reports.order-history');
            Route::get('reports/referral-transactions', [Client\ReportController::class, 'referralTransactions'])->name('reports.referral-transactions');
            Route::get('reports/launchpad-transactions', [Client\ReportController::class, 'launchpadTransactions'])->name('reports.launchpad-transactions');
            Route::get('reports/futures-trades', [Client\ReportController::class, 'futuresTrades'])->name('reports.trades.futures');

            Route::middleware('auth:sanctum')->get('/user', function (\Illuminate\Http\Request $request) {
                return $request->user();
            });
        });

        // oAuth redirect and callback urls
        Route::get('/login/google', [Client\GoogleLoginController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/login/google/callback', [Client\GoogleLoginController::class, 'handleGoogleCallback']);
        Route::get('/auth/twitter', [Client\TwitterController::class, 'redirectToTwitter'])->name('auth.twitter');
        Route::get('/auth/twitter/callback', [Client\TwitterController::class, 'handleTwitterCallback']);

        /*
         * Public Static Pages
         */

        Route::get('/short/{slug}', [Client\PageController::class, 'showShort'])->name('page.show.short');

        Route::get('{slug}', [Client\PageController::class, 'show'])->name('page.show');

    });


});
