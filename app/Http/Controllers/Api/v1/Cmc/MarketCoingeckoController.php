<?php
/*
 *  Copyright 2021. CCTech Smart Solutions, LLC
 *  Protected with Proprietary License
 */

namespace App\Http\Controllers\Api\v1\Cmc;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Market\MarketCoingeckoDataRequest;
use App\Http\Resources\Market\Coingecko\PairCoingecko;
use App\Http\Resources\Market\Coingecko\TickerCoingecko;
use App\Http\Resources\Market\Coingecko\TransactionCoingecko;
use App\Models\Market\Market;
use App\Models\Order\Order;
use App\Repositories\Market\MarketRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Market\MarketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/*
 * This controller used to provide with the required API endpoints for Coingecko
 */

class MarketCoingeckoController extends Controller
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
     * Display a listing of the resource for market pairs.
     *
     * @return \Illuminate\Http\Response
     */
    public function pairs(Request $request)
    {
        return PairCoingecko::collection($this->marketService->getMarkets(false));
    }

    /**
     * Display a listing of the resource for the tickers.
     *
     * @return \Illuminate\Http\Response
     */
    public function tickers(Request $request)
    {
        return TickerCoingecko::collection($this->marketService->getMarkets(false));
    }

    /**
     * Display a listing of bids/asks per marker orderbook.
     *
     * @return \Illuminate\Http\Response
     */
    public function orderbook(MarketCoingeckoDataRequest $request) {

        $market = $request->get('ticker_id');

        $model = (new MarketRepository())->get($market, false, false, true);

        $orderRepository = new OrderRepository();

        $asks = collect($orderRepository
            ->get($model->name, Order::SIDE_SELL))
            ->map(function ($item) use ($model) {
                return [
                    'price' => math_formatter($item->price, $model->quote_precision),
                    'quantity' => $item->quantity
                ];
            })
            ->merge(Cache::get("markets_liquidity.{$model->name}.asks"))
            ->sortBy('price')
            ->groupBy(['price'])
            ->map(function ($item) use ($model) {
            return [
                math_formatter($item->first()['price'], $model->quote_precision),
                (string)$item->sum('quantity')
            ];
        })->values();

        $bids = collect($orderRepository
            ->get($model->name, Order::SIDE_BUY))
            ->map(function ($item) use ($model) {
                return [
                    'price' => math_formatter($item->price, $model->quote_precision),
                    'quantity' => $item->quantity
                ];
            })
            ->merge(Cache::get("markets_liquidity.{$model->name}.bids"))
            ->sortByDesc('price')->groupBy(['price'])
            ->map(function ($item) use ($model) {
            return [
                math_formatter($item->first()['price'], $model->quote_precision),
                (string)$item->sum('quantity')
            ];
        })->values();

        return [
            'ticker_id' => $market,
            'timestamp' => Carbon::now()->getTimestampMs(),
            'bids' => $bids,
            'asks' => $asks,
        ];
    }

    /**
     * Display a listing of market trades
     *
     * @return \Illuminate\Http\Response
     */
    public function historicalTrades(MarketCoingeckoDataRequest $request)
    {
        $market = $request->get('ticker_id');
        $limit = $request->get('limit');

        $bids = TransactionCoingecko::collection($this->marketService->getTrades($market, $limit, Order::SIDE_BUY, true));

        $asks = TransactionCoingecko::collection($this->marketService->getTrades($market, $limit, Order::SIDE_SELL, true));

        return [
            'bids' => $bids,
            'asks' => $asks
        ];
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
}
