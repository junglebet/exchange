<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wallet\Gateways\Stripe\StripePaymentFormRequest;
use App\Http\Requests\Api\Wallet\GetAddressRequest;
use App\Http\Requests\Api\Wallet\WithdrawRequest;
use App\Http\Resources\Wallet\Deposit\Deposit;
use App\Http\Resources\Wallet\Deposit\FiatDeposit;
use App\Http\Resources\Wallet\WalletCollection;
use App\Http\Resources\Wallet\Withdrawal\FiatWithdrawal;
use App\Http\Resources\Wallet\Withdrawal\Withdrawal;
use App\Models\Network\Network;
use App\Http\Resources\Network\NetworkLite;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Deposit\DepositRepository;
use App\Repositories\Deposit\FiatDepositRepository;
use App\Repositories\Withdrawal\FiatWithdrawalRepository;
use App\Repositories\Withdrawal\WithdrawalRepository;
use App\Services\Currency\CurrencyService;
use App\Services\PaymentGateways\Fiat\Stripe\Model\StripeModel;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class WalletController extends Controller
{
    /**
     * @var walletService
     */
    protected $walletService;

    /**
     * @param WalletService $walletService
     *
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new WalletCollection($this->walletService->getWallets());
    }

    /**
     * Get wallet address.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAddress(GetAddressRequest $request)
    {
        $symbol = request()->get('symbol', null);
        $network = request()->get('network', null);

        $result = (new WalletService())->generateWalletAddress($symbol, auth()->user()->getAuthIdentifier(), $network);

        return response()->json($result['data'], $result['status']);
    }

    /**
     * Get deposits.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDeposits(Request $request)
    {
        $depositRepository = new DepositRepository();

        $deposits = Deposit::collection($depositRepository->getReportUser(auth()->user(), false))->response()->getData(true);

        return response()->json($deposits);
    }

    /**
     * Get fiat deposits.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFiatDeposits(Request $request, $type = 'all')
    {
        $depositRepository = new FiatDepositRepository();

        $deposits = FiatDeposit::collection($depositRepository->getReportUser(auth()->user(), 'all', false))->response()->getData(true);

        return response()->json($deposits);
    }

    /**
     * Get withdrawals.
     *
     * @return \Illuminate\Http\Response
     */
    public function getWithdrawals(Request $request, $type = 'coin')
    {
        $withdrawalRepository = new WithdrawalRepository();

        $withdrawals = Withdrawal::collection($withdrawalRepository->getReportUser(auth()->user(), false))->response()->getData(true);

        return response()->json($withdrawals);
    }

    /**
     * Get fiat withdrawals.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFiatWithdrawals(Request $request)
    {
        $withdrawalRepository = new FiatWithdrawalRepository();

        $withdrawals = FiatWithdrawal::collection($withdrawalRepository->getReportUser(auth()->user(), false))->response()->getData(true);

        return response()->json($withdrawals);
    }

    /**
     * Withdraw Function
     *
     * @return \Illuminate\Http\Response
     */
    public function withdraw(WithdrawRequest $request)
    {
        if(!$request->user()->tokenCan('withdraw')) {
            return response()->json(['message' => 'Unauthorized'], STATUS_FORBIDDEN);
        }

        $symbol = request()->get('symbol', null);
        $network = request()->get('network', null);
        $address = request()->get('address', null);
        $amount = request()->get('amount', null);
        $payment_id = request()->get('payment_id', null);

        $result = (new WalletService())->withdrawCrypto($symbol, $address, $amount, auth()->user()->getAuthIdentifier(), $network, $payment_id);

        return response()->json(['result' => $result]);
    }

    public function stripePaymentValidate(StripePaymentFormRequest $request) {
        return response()->json(['success' => true]);
    }

    public function stripePayment(StripePaymentFormRequest $request) {

        Stripe::setApiKey(setting('stripe.secret_key'));

        $baseCurrency = mb_strtolower(setting('stripe.currency', 'usd'));

        try {

            $zeroCurrencies = config('stripe.zero_currencies');

            $currency = (new CurrencyRepository())->get($request->get('currency_id'));

            $amount = $request->get('amount');

            $actualAmount = math_multiply($amount, $currency->cc_exchange_rate);

            if(!in_array(mb_strtoupper($baseCurrency), $zeroCurrencies)) {
                $actualAmount = math_multiply($actualAmount, 100);
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => intval($actualAmount),
                'currency' => $baseCurrency,
            ]);

            $intent = new StripeModel();
            $intent->intent_id = $paymentIntent->id;
            $intent->status = 'pending';
            $intent->currency_id = $request->get('currency_id');
            $intent->user_id = auth()->user()->getAuthIdentifier();
            $intent->save();

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];

            return response()->json($output);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function getNetworks() {

        $networks = Network::where('type', 'coin')->where('id', '!=', NETWORK_COINPAYMENTS)->get();

        $withdrawals = NetworkLite::collection($networks);

        return response()->json($withdrawals);
    }

    /**
     * Load networks
     *
     * @return \Illuminate\Http\Response
     */
    public function loadNetworks(Request $request)
    {
        $symbol = $request->get('symbol');

        if(!$symbol) {
            return response()->json(['success' => false]);
        }

        $currency = (new CurrencyService())->getCurrencyBySymbol($symbol, 'coin', true, ['networks', 'file']);

        if(!$currency) {
            return response()->json(['success' => false]);
        }

        $networks = [];

        foreach($currency->networks as $network) {

            if($network->id == NETWORK_COINPAYMENTS) {
                $network->name = $currency->coinpayments_description;
            }

            $networks[$network->id] = $network->name;
        }

        return response()->json($networks);
    }
}
