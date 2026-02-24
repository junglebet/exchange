<?php

namespace App\Repositories\Currency;

use App\Interfaces\Currency\CurrencyRepositoryInterface;
use App\Models\Currency\Currency;
use App\Models\Market\Market;
use App\Models\User\User;
use App\Models\Wallet\Wallet;
use App\Models\Withdrawal\FiatWithdrawal;
use App\Models\Withdrawal\Withdrawal;
use App\Services\PaymentGateways\Coin\Bnb\Api\BnbGateway;
use App\Services\PaymentGateways\Coin\Coinpayments\Model\CoinpaymentsCurrency;
use App\Services\PaymentGateways\Coin\Ethereum\Api\EthereumGateway;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * CurrencyRepository constructor.
     *
     */
    public function __construct()
    {
        $this->currency = new Currency();
    }

    public function get($id, $trashed = false, $dashboard = false, $relations = null) {

        $currency = Currency::whereId($id);

        if($relations === null) {
            $currency->with(['networks', 'file']);
        } else {
            $currency->with($relations);
        }

        if(!$dashboard) {
            $currency->active();
        }

        if($trashed) {
            $currency->withTrashed();
        }

        return $currency->first();
    }

    public function getCurrencyBySymbol($symbol, $type = false, $active = true, $relations = null) {

        $currency = Currency::query();

        $currency->where(function($query) use ($symbol){
            $query->where('alt_symbol', $symbol);
            $query->orWhere('symbol', $symbol);
        });

        if(!$relations) {
            $relations = ['networks'];
        }

        $currency->with($relations);

        if($type) {
            $currency->whereType($type);
        }

        if($active) {
            $currency->active();
        }

        return $currency->first();
    }

    public function getCurrencyByName($name, $type = false, $active = true, $relations = null) {

        $currency = Currency::query();

        $currency->where('name', $name);

        if(!$relations) {
            $relations = ['networks'];
        }

        $currency->with($relations);

        if($type) {
            $currency->whereType($type);
        }

        if($active) {
            $currency->active();
        }

        return $currency->first();
    }

    public function getCurrencyByContract($contract, $type = false, $active = true) {

        $currency = Currency::query();
        $currency->where('contract', "ILIKE", $contract);
        $currency->orWhere('bep_contract', "ILIKE", $contract);
        $currency->orWhere('trc_contract', "ILIKE", $contract);
        $currency->orWhere('matic_contract', "ILIKE", $contract);

        $currency->with('networks');

        if($type) {
            $currency->whereType($type);
        }

        if($active) {
            $currency->active();
        }

        return $currency->first();
    }

    public function all($paginate, $dashboard = false, $relations = ['file'], $type = false) {

        $currencies = Currency::filter(request()->only(['search', 'trashed', 'type']))->orderByLatest();

        if(!$dashboard) {
            $currencies->active();
        }

        if($type) {
            $currencies->type($type);
        }

        $currencies->with($relations);

        if($paginate) {
            return $currencies->paginate(30)->withQueryString();
        } else {
            return $currencies->get();
        }
    }

    public function count() {
        $currency = Currency::query();
        return $currency->count();
    }

    public function store($data) {

        if(in_array(NETWORK_COINPAYMENTS, $data['networks'])) {
            $coinpaymentCurrency = CoinpaymentsCurrency::where('symbol', $data['alt_symbol'])->first();
            $data['txn_explorer'] = $coinpaymentCurrency->blockchain_url;
            $data['has_payment_id'] = $coinpaymentCurrency && $coinpaymentCurrency->has_payment_id;
        }

        if(in_array(NETWORK_ETH, $data['networks']) || in_array(NETWORK_BNB, $data['networks'])) {
            $data['decimals'] = 8;
        }

        if(in_array(NETWORK_ERC, $data['networks']) || in_array(NETWORK_ETH, $data['networks'])) {
            $data['txn_explorer'] = 'https://etherscan.io/tx/%txid%';
        }

        if(in_array(NETWORK_BEP, $data['networks']) || in_array(NETWORK_BNB, $data['networks'])) {
            $data['txn_explorer'] = 'https://bscscan.com/tx/%txid%';
        }

        if(in_array(NETWORK_MATIC, $data['networks']) || in_array(NETWORK_MATIC20, $data['networks'])) {
            $data['txn_explorer'] = 'https://polygonscan.com/tx/%txid%';
        }

        if(in_array(NETWORK_TRX, $data['networks']) || in_array(NETWORK_TRC, $data['networks'])) {
            $data['txn_explorer'] = 'https://tronscan.org/#/transaction/%txid%';
        }

        if(in_array(NETWORK_BTC, $data['networks']) || in_array(NETWORK_BRC20, $data['networks'])) {
            $data['txn_explorer'] = 'https://www.blockchain.com/btc/tx/%txid%';
        }

        $currency = $this->currency->create($data);
        $currency->networks()->sync($data['networks']);

        return $currency->fresh();
    }

    public function update($id, $data) {

        $currency = Currency::withTrashed()->find($id);
        $currency->update($data);
        $currency->networks()->sync($data['networks']);

        return $currency->fresh();
    }

    public function delete($id) {

        $currency = Currency::find($id);
        $currency->symbol = $currency->symbol . time();
        $currency->alt_symbol = $currency->alt_symbol . time();
        $currency->save();
        $currency->delete();

        return true;
    }

    public function restore($id) {

        $currency = Currency::withTrashed()->find($id);
        $currency->restore();

        return true;
    }

    public function getQuoteCurrencies() {
        return DB::table('currencies')->select('symbol')->whereRaw('id IN (SELECT quote_currency_id FROM markets WHERE deleted_at IS NULL GROUP BY quote_currency_id)')->orderBy('symbol','asc')->pluck('symbol');
    }

    public function getReport($filters = [], $pagination = true) {

        $currency = Currency::query();

        $currency->with(['networks']);

        $currency->filter($filters)->orderBy('name', 'asc');

        if(!$pagination) {
            return $currency->get();
        }

        return $currency->paginate(150)->withQueryString();
    }

    public function getDailyAvailableWithdrawal(Currency $currency, User $user) {

        $dailyLimit = $this->getDailyWithdrawalLimit();

        $price = $this->currencyPriceInUsd($currency);

        $limit = $dailyLimit['verified'];

        if(!$user->kyc_verified_at && $dailyLimit['unverified']) {
            $limit = $dailyLimit['unverified'];
        }

        $enabled = (double)$price > 0 && $limit > 0;

        if($enabled) {
            $finalAmount = (double)$limit > 0 ? math_divide($limit, $price) : 0;
        } else {
            $finalAmount = 0;
        }

        $withdrawalModel = $currency->type == "coin" ? Withdrawal::query() : FiatWithdrawal::query();

        $userLimit = $withdrawalModel->where('user_id', $user->id)->whereNotIn('status', [WITHDRAWAL_REJECTED, WITHDRAWAL_REJECTED])->where('updated_at', '>=', Carbon::now()->subDay()->toDateTimeString())->sum('inusd');

        $remainingAvailableAmount = math_sub($finalAmount, $enabled ? math_divide($userLimit, $price) : 0);

        $available = $enabled && $remainingAvailableAmount > 0 ? $remainingAvailableAmount : 0;

        return [
            'status' => $enabled,
            'available' => math_formatter($available, $currency->decimals),
            'total' => math_formatter($finalAmount, $currency->decimals)
        ];
    }

    public function getDailyWithdrawalLimit() {

        $verified = setting('general.withdrawal_limit', 0);
        $unverified = setting('general.withdrawal_limit_kyc', 0);

        return [
            'verified' => $verified,
            'unverified' => $unverified
        ];
    }

    public function currencyPriceInUsd($currency, $usdtCurrency = false, $usdCurrency = false, $market = false) {

        // Quote currencies to calculate the price from any coin pair with this currency
        if(!$usdtCurrency) {
            $usdtCurrency = $this->getCurrencyBySymbol('USDT');
        }

        if(!$usdCurrency) {
            $usdCurrency = $this->getCurrencyBySymbol('USD');
        }

        if($usdtCurrency && $usdtCurrency->id == $currency->id) {
            return 1;
        }

        if($usdCurrency && $usdCurrency->id == $currency->id) {
            return 1;
        }

        if(!$usdtCurrency)
            return 0;

        if($market) {
            return market_get_stats($market, 'last');
        }

        $market = Market::where('quote_currency_id', $usdtCurrency->id)->where('base_currency_id', $currency->id)->first();

        if(!$market) {
            return 0;
        }

        return market_get_stats($market->id, 'last');
    }

    public function calculateAmountInUsd($currency, $amount) {

        $rateInUsd = (new CurrencyRepository())->currencyPriceInUsd($currency);

        if($rateInUsd == 0) {
            return 0;
        }

        return math_formatter($amount / $rateInUsd, 8);
    }
}
