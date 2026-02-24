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

    Route::group(['middleware' => ['language.detect', 'maintenance', 'ping']], function () {

        Route::group(['prefix' => 'exchange-control-panel'], function () {
            Route::get('admin-login', [Admin\DashboardController::class, 'login'])->name('admin.login');
        });

        Route::group(['prefix' => 'exchange-control-panel', 'middleware' => ['role:admin']], function () {

            Route::get('', [Admin\DashboardController::class, 'index'])->name('admin.dashboard');

            Route::group(['middleware' => ['role:user_editor']], function()
            {
                /*
                 * Users
                 */
                Route::get('users/fetch', [Admin\UserController::class, 'fetch'])->name('admin.users.fetch');

                Route::get('users', [Admin\UserController::class, 'index'])->name('admin.users');
                Route::get('users/{user}/edit', [Admin\UserController::class, 'edit'])->name('admin.users.edit');
                Route::put('users/{user}', [Admin\UserController::class, 'update'])->name('admin.users.update');
                Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

                /*
                 * Kyc Documents
                 */
                Route::get('kyc-documents', [Admin\KycDocumentController::class, 'index'])->name('admin.kyc.documents');
                Route::put('kyc-documents/{document}', [Admin\KycDocumentController::class, 'moderate'])->name('admin.kyc.moderate');
            });

            Route::group(['middleware' => ['role:finance_manager']], function()
            {
                /*
                 * Reports
                 */
                Route::get('reports', [Admin\ReportController::class, 'index'])->name('admin.reports');
                Route::get('reports/deposits', [Admin\ReportController::class, 'deposits'])->name('admin.reports.deposits');
                Route::post('reports/deposits/resync', [Admin\ReportController::class, 'resyncDeposit'])->name('admin.reports.deposits.resync');

                Route::get('reports/fiat-deposits', [Admin\ReportController::class, 'fiatDeposits'])->name('admin.reports.deposits.fiat');
                Route::get('reports/withdrawals', [Admin\ReportController::class, 'withdrawals'])->name('admin.reports.withdrawals');
                Route::get('reports/fiat-withdrawals', [Admin\ReportController::class, 'fiatWithdrawals'])->name('admin.reports.withdrawals.fiat');

                Route::put('reports/withdrawals/{withdrawal}', [Admin\ReportController::class, 'moderateWithdrawal'])->name('admin.reports.withdrawals.moderate');
                Route::get('reports/trades', [Admin\ReportController::class, 'trades'])->name('admin.reports.trades');
                Route::get('reports/referral-transactions', [Admin\ReportController::class, 'referralTransactions'])->name('admin.reports.referral-transactions');
                Route::get('reports/launchpad-transactions', [Admin\ReportController::class, 'launchpadTransactions'])->name('admin.reports.launchpad-transactions');
                Route::get('reports/staking-transactions', [Admin\ReportController::class, 'stakingTransactions'])->name('admin.reports.staking-transactions');


                Route::put('reports/fiat-deposits/{deposit}', [Admin\ReportController::class, 'moderateFiatDeposit'])->name('admin.reports.deposits.fiat.moderate');
                Route::put('reports/fiat-withdrawals/{withdrawal}', [Admin\ReportController::class, 'moderateFiatWithdrawal'])->name('admin.reports.withdrawals.fiat.moderate');

                Route::get('reports/wallets/system', [Admin\ReportController::class, 'systemWallets'])->name('admin.reports.wallets.system');
                Route::get('reports/wallets', [Admin\ReportController::class, 'wallets'])->name('admin.reports.wallets');
                Route::post('reports/wallets/transfer', [Admin\ReportController::class, 'transferWallets'])->name('admin.reports.wallets.fund');
                Route::get('reports/finances', [Admin\ReportController::class, 'finances'])->name('admin.reports.finances');
                Route::get('reports/finances/fetch', [Admin\ReportController::class, 'financesFetch'])->name('admin.reports.finances.fetch');

                /*
                 * Bank Accounts
                 */
                Route::get('bank-accounts', [Admin\BankAccountController::class, 'index'])->name('admin.bank_accounts');
                Route::get('bank-accounts/create', [Admin\BankAccountController::class, 'create'])->name('admin.bank_accounts.create');
                Route::post('bank-accounts', [Admin\BankAccountController::class, 'store'])->name('admin.bank_accounts.store');
                Route::get('bank-accounts/{bankAccount}/edit', [Admin\BankAccountController::class, 'edit'])->name('admin.bank_accounts.edit');
                Route::put('bank-accounts/{bankAccount}', [Admin\BankAccountController::class, 'update'])->name('admin.bank_accounts.update');
                Route::delete('bank-accounts/{bankAccount}', [Admin\BankAccountController::class, 'destroy'])->name('admin.bank_accounts.destroy');

            });

            Route::group(['middleware' => ['role:content_editor']], function()
            {
                /*
                 * Articles
                 */
                Route::get('articles', [Admin\ArticleController::class, 'index'])->name('admin.articles');
                Route::get('articles/create', [Admin\ArticleController::class, 'create'])->name('admin.articles.create');
                Route::post('articles', [Admin\ArticleController::class, 'store'])->name('admin.articles.store');
                Route::get('articles/{article}/edit', [Admin\ArticleController::class, 'edit'])->name('admin.articles.edit');
                Route::put('articles/{article}', [Admin\ArticleController::class, 'update'])->name('admin.articles.update');
                Route::delete('articles/{article}', [Admin\ArticleController::class, 'destroy'])->name('admin.articles.destroy');
                Route::put('articles/{article}/restore', [Admin\ArticleController::class, 'restore'])->name('admin.articles.restore');

                /*
                 * Pages
                 */
                Route::get('pages', [Admin\PageController::class, 'index'])->name('admin.pages');
                Route::get('pages/create', [Admin\PageController::class, 'create'])->name('admin.pages.create');
                Route::post('pages', [Admin\PageController::class, 'store'])->name('admin.pages.store');
                Route::get('pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('admin.pages.edit');
                Route::put('pages/{page}', [Admin\PageController::class, 'update'])->name('admin.pages.update');
                Route::delete('pages/{page}', [Admin\PageController::class, 'destroy'])->name('admin.pages.destroy');

                /*
                 * Languages
                 */
                Route::get('languages', [Admin\LanguageController::class, 'index'])->name('admin.languages');
                Route::get('languages/create', [Admin\LanguageController::class, 'create'])->name('admin.languages.create');
                Route::post('languages', [Admin\LanguageController::class, 'store'])->name('admin.languages.store');
                Route::get('languages/{language}/edit', [Admin\LanguageController::class, 'edit'])->name('admin.languages.edit');
                Route::put('languages/{language}', [Admin\LanguageController::class, 'update'])->name('admin.languages.update');
                Route::delete('languages/{language}', [Admin\LanguageController::class, 'destroy'])->name('admin.languages.destroy');

                /*
                 * Language Translations
                 */
                Route::post('languages-translations/sync/{language}', [Admin\LanguageController::class, 'sync'])->name('admin.language.translations.sync');
                Route::get('languages-translations/{language}', [Admin\LanguageController::class, 'translations'])->name('admin.language.translations');
                Route::put('languages-translations/store/{language}', [Admin\LanguageController::class, 'translationsStore'])->name('admin.language.translations.store');
                Route::put('languages-translations/update/{language}', [Admin\LanguageController::class, 'translationsUpdate'])->name('admin.language.translations.update');
                Route::delete('languages-translations/{language}/{translation}', [Admin\LanguageController::class, 'translationsDestroy'])->name('admin.language.translations.destroy');

            });

            Route::group(['middleware' => ['role:assets_editor']], function() {

                /*
                 * Markets
                 */
                Route::get('markets', [Admin\MarketController::class, 'index'])->name('admin.markets');
                Route::get('markets/create', [Admin\MarketController::class, 'create'])->name('admin.markets.create');
                Route::post('markets', [Admin\MarketController::class, 'store'])->name('admin.markets.store');
                Route::get('markets/{market}/edit', [Admin\MarketController::class, 'edit'])->name('admin.markets.edit');
                Route::put('markets/{market}', [Admin\MarketController::class, 'update'])->name('admin.markets.update');
                Route::delete('markets/{market}', [Admin\MarketController::class, 'destroy'])->name('admin.markets.destroy');
                Route::put('markets/{market}/restore', [Admin\MarketController::class, 'restore'])->name('admin.markets.restore');

                /*
                 * Currencies
                 */
                Route::get('currencies', [Admin\CurrencyController::class, 'index'])->name('admin.currencies');
                Route::get('currencies/create', [Admin\CurrencyController::class, 'create'])->name('admin.currencies.create');
                Route::post('currencies', [Admin\CurrencyController::class, 'store'])->name('admin.currencies.store');
                Route::get('currencies/{currency}/edit', [Admin\CurrencyController::class, 'edit'])->name('admin.currencies.edit');
                Route::put('currencies/{currency}', [Admin\CurrencyController::class, 'update'])->name('admin.currencies.update');
                Route::delete('currencies/{currency}', [Admin\CurrencyController::class, 'destroy'])->name('admin.currencies.destroy');
                Route::put('currencies/{currency}/restore', [Admin\CurrencyController::class, 'restore'])->name('admin.currencies.restore');
                Route::get('currencies/coinpayments/coins', [Admin\CurrencyController::class, 'getCoinpaymentsCoins'])->name('admin.currencies.coinpayments.coins');
                Route::get('currencies/coinpayments/sync', [Admin\CurrencyController::class, 'syncCoinpaymentsCoins'])->name('admin.currencies.coinpayments.sync');

                /*
                 * Networks
                 */
                Route::get('networks', [Admin\NetworkController::class, 'index'])->name('admin.networks');
                Route::get('networks/{network}/edit', [Admin\NetworkController::class, 'edit'])->name('admin.networks.edit');
                Route::put('networks/{network}', [Admin\NetworkController::class, 'update'])->name('admin.networks.update');

                /*
                 * Liquidity Module
                 */
                Route::get('liquidity', [Admin\LiquidityController::class, 'index'])->name('admin.liquidity');
                Route::post('liquidity/run', [Admin\LiquidityController::class, 'run'])->name('admin.liquidity.run');
                Route::post('liquidity/stop', [Admin\LiquidityController::class, 'stop'])->name('admin.liquidity.stop');
            });

            Route::group(['middleware' => ['role:superadmin']], function() {

                /*
                 * Settings
                 */
                Route::get('settings', [Admin\SettingsController::class, 'index'])->name('admin.settings');
                Route::put('settings', [Admin\SettingsController::class, 'update'])->name('admin.settings.update');

                /*
                 * System Monitor
                 */
                Route::get('system-monitor/test', [Admin\SystemMonitorController::class, 'test'])->name('admin.system.monitor.test');
                Route::post('system-monitor/websocket', [Admin\SystemMonitorController::class, 'websocket'])->name('admin.system.monitor.websocket');

                /*
                 * Peer Trade
                 */
                Route::get('peer-trades-payment-methods', [Admin\PeerPaymentMethodController::class, 'index'])->name('admin.peerPaymentMethods');
                Route::get('peer-trades-payment-methods/create', [Admin\PeerPaymentMethodController::class, 'create'])->name('admin.peerPaymentMethods.create');
                Route::post('peer-trades-payment-methods', [Admin\PeerPaymentMethodController::class, 'store'])->name('admin.peerPaymentMethods.store');
                Route::get('peer-trades-payment-methods/{paymentMethod}/edit', [Admin\PeerPaymentMethodController::class, 'edit'])->name('admin.peerPaymentMethods.edit');
                Route::put('peer-trades-payment-methods/{paymentMethod}', [Admin\PeerPaymentMethodController::class, 'update'])->name('admin.peerPaymentMethods.update');
                Route::delete('peer-trades-payment-methods/{paymentMethod}', [Admin\PeerPaymentMethodController::class, 'destroy'])->name('admin.peerPaymentMethods.destroy');

                Route::get('peer-trades-payment-fields/{paymentMethod}/create', [Admin\PeerPaymentFieldController::class, 'create'])->name('admin.peerPaymentFields.create');
                Route::get('peer-trades-payment-fields/{paymentMethod}', [Admin\PeerPaymentFieldController::class, 'index'])->name('admin.peerPaymentFields');
                Route::post('peer-trades-payment-fields', [Admin\PeerPaymentFieldController::class, 'store'])->name('admin.peerPaymentFields.store');
                Route::get('peer-trades-payment-fields/{paymentMethod}/{paymentField}/edit', [Admin\PeerPaymentFieldController::class, 'edit'])->name('admin.peerPaymentFields.edit');
                Route::put('peer-trades-payment-fields/{paymentField}', [Admin\PeerPaymentFieldController::class, 'update'])->name('admin.peerPaymentFields.update');
                Route::delete('peer-trades-payment-fields/{paymentField}', [Admin\PeerPaymentFieldController::class, 'destroy'])->name('admin.peerPaymentFields.destroy');


                /*
                 * Stakings
                 */
                Route::get('staking', [Admin\StakingController::class, 'index'])->name('admin.stakings');
                Route::get('staking/create', [Admin\StakingController::class, 'create'])->name('admin.stakings.create');
                Route::post('staking', [Admin\StakingController::class, 'store'])->name('admin.stakings.store');
                Route::get('staking/{staking}/edit', [Admin\StakingController::class, 'edit'])->name('admin.stakings.edit');
                Route::put('staking/{staking}', [Admin\StakingController::class, 'update'])->name('admin.stakings.update');
                Route::delete('staking/{staking}', [Admin\StakingController::class, 'destroy'])->name('admin.stakings.destroy');

                /*
                * Cold Storage
                */
                Route::get('cold-storage', [Admin\ColdStorageController::class, 'index'])->name('admin.cold_storage');
                Route::get('cold-storage/create', [Admin\ColdStorageController::class, 'create'])->name('admin.cold_storage.create');
                Route::post('cold-storage', [Admin\ColdStorageController::class, 'store'])->name('admin.cold_storage.store');
                Route::get('cold-storage/{coldStorage}/edit', [Admin\ColdStorageController::class, 'edit'])->name('admin.cold_storage.edit');
                Route::put('cold-storage/{coldStorage}', [Admin\ColdStorageController::class, 'update'])->name('admin.cold_storage.update');
                Route::delete('cold-storage/{coldStorage}', [Admin\ColdStorageController::class, 'destroy'])->name('admin.cold_storage.destroy');

                /*
                * Vouchers
                */
                Route::get('vouchers', [Admin\VoucherController::class, 'index'])->name('admin.vouchers');
                Route::get('vouchers/create', [Admin\VoucherController::class, 'create'])->name('admin.vouchers.create');
                Route::post('vouchers', [Admin\VoucherController::class, 'store'])->name('admin.vouchers.store');
                Route::get('vouchers/{voucher}/edit', [Admin\VoucherController::class, 'edit'])->name('admin.vouchers.edit');
                Route::put('vouchers/{voucher}', [Admin\VoucherController::class, 'update'])->name('admin.vouchers.update');
                Route::delete('vouchers/{voucher}', [Admin\VoucherController::class, 'destroy'])->name('admin.vouchers.destroy');

                /*
                 * Launchpads
                 */
                Route::get('launchpads', [Admin\LaunchpadController::class, 'index'])->name('admin.launchpads');
                Route::get('launchpads/create', [Admin\LaunchpadController::class, 'create'])->name('admin.launchpads.create');
                Route::post('launchpads', [Admin\LaunchpadController::class, 'store'])->name('admin.launchpads.store');
                Route::get('launchpads/{launchpad}/edit', [Admin\LaunchpadController::class, 'edit'])->name('admin.launchpads.edit');
                Route::put('launchpads/{launchpad}', [Admin\LaunchpadController::class, 'update'])->name('admin.launchpads.update');
                Route::delete('launchpads/{launchpad}', [Admin\LaunchpadController::class, 'destroy'])->name('admin.launchpads.destroy');
            });

            /*
            * File Upload
            */
            Route::post('/upload', [Admin\FileController::class, 'upload'])->name('file-upload');
            Route::delete('/upload', [Admin\FileController::class, 'delete'])->name('file-delete');

        });

    });


});

require_once __DIR__ . '/common.php';
