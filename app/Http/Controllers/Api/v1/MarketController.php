<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Market\MarketDataRequest;
use App\Http\Resources\Market\Market;
use App\Models\Market\Market as MarketModel;
use App\Http\Resources\Market\Market as MarketResource;
use App\Http\Resources\Market\MarketCollection;
use App\Http\Resources\Transaction\Candles\CandleCollection;
use App\Http\Resources\Transaction\TransactionCollection;
use App\Models\Order\Order;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\Market\MarketRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Liquidity\Binance\BinanceApi;
use App\Services\Market\MarketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MarketController extends Controller
{
    /**
     * @var marketService
     */
    protected $marketService;

    /**
     * PostController Constructor
     *
     * @param MarketService $marketService
     *
     */
    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ticker(Request $request)
    {
        $market = $request->get('market', null);

        if($market) {
            return new Market($this->marketService->getMarket($market));
        }

        return new MarketCollection($this->marketService->getMarkets(false));
    }

    public function orderbook(MarketDataRequest $request) {

        $market = $request->get('market');

        $model = MarketModel::whereName($market)->first();

        $orderRepository = new OrderRepository();

        $asks = collect($orderRepository
            ->get($market, Order::SIDE_SELL))
            ->map(function ($item) use ($model) {
                return [
                    'price' => math_formatter($item->price, $model->quote_precision),
                    'quantity' => $item->quantity
                ];
            })
            ->merge(Cache::get("markets_liquidity.$market.asks"))
            ->sortBy('price')
            ->groupBy(['price'])
            ->map(function ($item) use ($model) {
            return [
                'price' => math_formatter($item->first()['price'], $model->quote_precision),
                'quantity' => $item->sum('quantity')
            ];
        })->values();

        $bids = collect($orderRepository
            ->get($market, Order::SIDE_BUY))
            ->map(function ($item) use ($model) {
                return [
                    'price' => math_formatter($item->price, $model->quote_precision),
                    'quantity' => $item->quantity
                ];
            })
            ->merge(Cache::get("markets_liquidity.$market.bids"))
            ->sortByDesc('price')
            ->groupBy(['price'])
            ->map(function ($item) use ($model) {
            return [
                'price' => math_formatter($item->first()['price'], $model->quote_precision),
                'quantity' => $item->sum('quantity')
            ];
        })->values();

        return [
            'bids' => $bids,
            'asks' => $asks,
        ];
    }

    /**
     * Display a listing of market trades
     *
     * @return \Illuminate\Http\Response
     */
    public function trades(MarketDataRequest $request)
    {
        $market = $request->get('market');

        return new TransactionCollection($this->marketService->getTrades($market));
    }

    /**
     * Display a listing of market trades
     *
     * @return \Illuminate\Http\Response
     */
    public function candles(MarketDataRequest $request)
    {
        return new CandleCollection($this->marketService->getCandles());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function marketInfo(Request $request)
    {
        $model = (new MarketRepository())->get($request->get('market'));

        if(!$model) {
            return response()->json(['result' => false, 'message' => __('Wrong market name')]);
        }

        $market = new MarketResource($model);


        return response()->json($market);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(null, STATUS_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return response()->json(null, STATUS_NOT_FOUND);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json(null, STATUS_NOT_FOUND);
    }

    public function historicalTrades(Request  $request) {

        $binance = new BinanceApi();

        $market = MarketModel::whereName($request->get('market'))->first();

        if(!$market) return response()->json(['success' => false]);

        $marketName = market_sanitize($market->name);

        $markets = Cache::get('markets_liquidity.active', []);

        if(isset($markets[$market->name])) {

            $trades = $binance->historicalTrades($marketName, '10');

            $sanitized = [];

            foreach($trades as $trade) {
                $sanitized[] = [
                    "created_at" => Carbon::createFromTimestampMs($trade['time']),
                    "price" => $trade['price'],
                    "quantity" => $trade['qty'],
                    "side" => $trade['isBuyerMaker'] ? 'buy' : 'sell'
                ];
            }

            return response()->json([
                'trades' => $sanitized,
                'success' => true,
            ]);
        }

        return response()->json([
            'trades' => [],
            'success' => false,
        ]);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function swap()
    {
        $currencyRepository = new CurrencyRepository();

        $defaultMarket = setting('general.swap_market', false);

        if($defaultMarket) {
            $defaultPair = MarketModel::whereName($defaultMarket)->first();
        } else {
            $defaultPair = MarketModel::first();
        }

        $currencyCollection = $currencyRepository->all(false);

        $currencies = [];

        $defaultBasePair = '';
        $defaultQuotePair = '';

        foreach ($currencyCollection as $key=>$currency) {
            $currencies[$currency->id] = [
                'name' => $currency->symbol,
                'id' => $currency->id,
                'logo' => $currency->file->path
            ];
        }

        if($defaultPair) {
            $defaultBasePair = $defaultPair->base_currency_id;
            $defaultQuotePair = $defaultPair->quote_currency_id;
        }

        return response()->json([
            'currencies' => $currencies,
            'defaultBasePair' => $defaultBasePair,
            'defaultQuotePair' => $defaultQuotePair,
        ]);
    }
}
