<?php

namespace App\Repositories\Wallet;

use App\Interfaces\Wallet\WalletRepositoryInterface;
use App\Models\Currency\Currency;
use App\Models\Wallet\Wallet;
use App\Models\Wallet\WalletAddress;
use App\Models\Withdrawal\Withdrawal;
use App\Repositories\Deposit\DepositRepository;
use App\Services\PaymentGateways\Coin\Bitcoin\Api\BitcoinGateway;
use App\Services\PaymentGateways\Coin\Bnb\Api\BnbGateway;
use App\Services\PaymentGateways\Coin\Coinpayments\Api\CoinpaymentsGateway;
use App\Services\PaymentGateways\Coin\Ethereum\Api\EthereumGateway;
use App\Services\PaymentGateways\Coin\Polygon\Api\PolygonGateway;
use App\Services\PaymentGateways\Coin\Tron\Api\TronGateway;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class WalletRepository implements WalletRepositoryInterface
{
    public function getWallets($user_id) {

        $wallet = Wallet::query();

        $wallet->with(['currency.file','address']);

        $wallet->has('currency');

        $wallet->where('user_id', $user_id);


        return $wallet->get();
    }

    public function getWallet($id) {
        return Wallet::find($id);
    }

    public function getWalletByCurrency($user_id, $currency, $lock = true) {

        $wallet = Wallet::query();

        $wallet->where('user_id', $user_id)->where('currency_id', $currency);

        if($lock) {
            $wallet->lockForUpdate();
        }

        return $wallet->first();
    }

    public function getWalletByAddress($address, $payment_id, $network, $currency) {

        $walletAddress = WalletAddress::query();

        $walletAddress->whereAddress($address);

        if(is_array($network)) {
            $walletAddress->whereIn('network_id', $network);
        } else {
            $walletAddress->where('network_id', $network);
        }

        if($payment_id) {
            $walletAddress->where('payment_id', $payment_id);
        }

        if(!$walletAddress->exists()) return null;

        $wallet = Wallet::query();

        $wallet->whereId($walletAddress->first()->wallet_id);

        if($currency) {
            $wallet->where('currency_id', $currency);
        }

        return $wallet->first();
    }

    public function getWalletAddress($wallet, $currency, $network) {

        try {

            $ethNetworks = [NETWORK_ETH, NETWORK_ERC, NETWORK_BNB, NETWORK_BEP, NETWORK_MATIC, NETWORK_MATIC20];
            $trcNetworks = [NETWORK_TRX, NETWORK_TRC];
            $btcNetworks = [NETWORK_BTC, NETWORK_BRC20];

            if(in_array($network->id, $ethNetworks)) {
                $walletAddress = WalletAddress::whereIn('network_id', $ethNetworks)->where('user_id', $wallet->user_id)->first();
            } elseif(in_array($network->id, $trcNetworks)) {
                $walletAddress = WalletAddress::whereIn('network_id', $trcNetworks)->where('user_id', $wallet->user_id)->first();
            } elseif(in_array($network->id, $btcNetworks)) {
                $walletAddress = WalletAddress::whereIn('network_id', $btcNetworks)->where('user_id', $wallet->user_id)->first();
            } else {
                $walletAddress = WalletAddress::where('wallet_id', $wallet->id)->first();
            }

            $address = $paymentId = null;

            if (!$walletAddress) {
                switch ($network->slug) {
                    case "coinpayments":
                        $generatedAddress = (new CoinpaymentsGateway())->createAddress($currency->alt_symbol);
                        $address = $generatedAddress['address'];
                        $paymentId = $generatedAddress['dest_tag'];
                        $private_key = null;
                        break;
                    case "eth":
                        $generatedAddress = (new EthereumGateway())->createEthAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "erc20":
                        $generatedAddress = (new EthereumGateway())->createErcAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "bnb":
                        $generatedAddress = (new BnbGateway())->createBnbAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "bep20":
                        $generatedAddress = (new BnbGateway())->createBepAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "matic":
                        $generatedAddress = (new PolygonGateway())->createMaticAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "matic20":
                        $generatedAddress = (new PolygonGateway())->createMatic20Address();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "trx":
                        $generatedAddress = (new TronGateway())->createTrcAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "trc20":
                        $generatedAddress = (new TronGateway())->createTrxAddress();
                        $address = $generatedAddress['address'];
                        $private_key = $generatedAddress['private_key'];
                        break;
                    case "brc20":
                    case "btc":
                        $generatedAddress = (new BitcoinGateway())->createBitcoinAddress();
                        $address = $generatedAddress;
                        $private_key = bitcoind()->dumpprivkey($generatedAddress);
                        break;
                }

                $walletAddress = new WalletAddress();
                $walletAddress->address = $address;
                $walletAddress->payment_id = $paymentId;
                $walletAddress->wallet_id = $wallet->id;
                $walletAddress->user_id = $wallet->user_id;
                $walletAddress->private_key = $private_key;
                $walletAddress->network_id = $network->id;
                $walletAddress->save();
            }

            return $walletAddress;

        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public function withdrawCrypto(Withdrawal $withdrawal, $txn = false) {

        $response = null;

        $amountAfterFee = math_sub($withdrawal->amount, $withdrawal->fee);

        try {
            switch ($withdrawal->network->slug) {
                case "internal":
                    $response = (new WalletService())->internalTransfer($withdrawal);
                    break;
                case "coinpayments":
                    $response = (new CoinpaymentsGateway())->withdraw($withdrawal->address, $withdrawal->payment_id, $amountAfterFee, $withdrawal->currency);
                    break;
                case "eth":
                    $response = (new EthereumGateway())->withdraw('eth', $withdrawal->id, $withdrawal->address, $amountAfterFee);
                    break;
                case "erc20":
                    $response = (new EthereumGateway())->withdraw('erc', $withdrawal->id, $withdrawal->address, $amountAfterFee, $withdrawal->currency->contract);
                    break;
                case "bnb":
                    $response = (new BnbGateway())->withdraw('bnb', $withdrawal->id, $withdrawal->address, $amountAfterFee);
                    break;
                case "bep20":
                    $response = (new BnbGateway())->withdraw('bep', $withdrawal->id, $withdrawal->address, $amountAfterFee, $withdrawal->currency->bep_contract);
                    break;
                case "matic":
                    $response = (new PolygonGateway())->withdraw('matic', $withdrawal->id, $withdrawal->address, $amountAfterFee);
                    break;
                case "matic20":
                    $response = (new PolygonGateway())->withdraw('matic20', $withdrawal->id, $withdrawal->address, $amountAfterFee, $withdrawal->currency->matic_contract);
                    break;
                case "trx":
                    $response = (new TronGateway())->withdraw('trx', $withdrawal->id, $withdrawal->address, $amountAfterFee);
                    break;
                case "trc20":
                    $response = (new TronGateway())->withdraw('trc', $withdrawal->id, $withdrawal->address, $amountAfterFee, $withdrawal->currency->trc_contract);
                    break;
                case "btc":
                    $response = (new BitcoinGateway())->withdraw('btc', $withdrawal, $withdrawal->address, $amountAfterFee);
                    break;
                case "brc20":
                    $response = (new BitcoinGateway())->withdrawBrc($withdrawal, $txn);
                    break;
            }

            return $response;

        } catch (\Exception $e) {
            Log::error($e);
            return [
                'source' => null,
                'status' => STATUS_VALIDATION_ERROR,
                'message' => 'withdraw_failed'
            ];
        }
    }

    public function store($data)
    {
        return Wallet::insert($data);
    }

    public function getReport() {

        $wallet = Wallet::query();

        $wallet->filter(request()->only(['search','type','referrer', 'user']))->orderByLatest();

        $wallet->with(['currency', 'user', 'address']);

        $wallet->has('currency')->has('user');

        return $wallet->paginate(100)->withQueryString();
    }

    public function depositInternal($wallet, $amount) {

        $depositRepository = new DepositRepository();

        $depositRepository->store([
            'deposit_id' => generate_uuid(),
            'txn' => null,
            'source_id' => null,
            'currency_id' => $wallet->currency->id,
            'type' => 'coin',
            'network_id' => NETWORK_INTERNAL,
            'amount' => $amount,
            'full_amount' => $amount,
            'network_fee' => 0,
            'address' => 'Internal',
            'user_id' => $wallet->user_id,
            'confirms' => 1,
            'status' => DEPOSIT_CONFIRMED,
            'internal_id' => generate_uuid(),
            'initial_raw' => null,
            'wallet_transfer_status' => 'processed'
        ]);

        (new WalletService())->increase($wallet, $amount);
    }
}
