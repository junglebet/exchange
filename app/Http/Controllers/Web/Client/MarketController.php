<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Market\Market as MarketResource;
use App\Http\Resources\Market\MarketCollection;
use App\Models\Market\Market;
use App\Repositories\Currency\CurrencyRepository;
use App\Services\Market\MarketService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Setting;

class MarketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(is_mobile_instance()) {
            return redirect()->route('markets.lite');
        }

        return Inertia::render('Market/Markets');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexLite()
    {
        return Inertia::render('MarketLite/Markets');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Market $market)
    {
        $market = (new MarketService())->getMarket($market->id);

        if(!$market) {
            throw new ModelNotFoundException();
        }

        if(is_mobile_instance()) {
            return redirect()->route('market.lite', $market->name);
        }

        $currencyRepository = (new CurrencyRepository())->getQuoteCurrencies();

        return Inertia::render('Market/Market', [
            "fee" => Setting::get('trade.taker_fee', INITIAL_TRADE_TAKER_FEE),
            'market' => new MarketResource($market),
            'quotes' => $currencyRepository,
            'futures' => false,
            'isMobile' => is_mobile_instance()
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLite(Market $market)
    {
        $market = (new MarketService())->getMarket($market->id);

        if(!$market) {
            throw new ModelNotFoundException();
        }

        $currencyRepository = (new CurrencyRepository())->getQuoteCurrencies();

        return Inertia::render('MarketLite/Market', [
            'market' => new MarketResource($market),
            'quotes' => $currencyRepository,
            'futures' => false,
            'isMobile' => is_mobile_instance()
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
            $defaultPair = Market::whereName($defaultMarket)->first();
        } else {
            $defaultPair = Market::first();
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

        return Inertia::render('Market/Swap', [
            'currencies' => $currencies,
            'defaultBasePair' => $defaultBasePair,
            'defaultQuotePair' => $defaultQuotePair,
        ]);
    }

    public function exchangeRate() {

        $base = request()->get('base');
        $quote = request()->get('quote');

        $market = Market::where(function($query) use ($base, $quote) {
            $query->where(function($query) use ($base, $quote) {
                $query->where('base_currency_id', $base);
                $query->where('quote_currency_id', $quote);
            })->orWhere(function($query) use ($base, $quote) {
                $query->where('base_currency_id', $quote);
                $query->where('quote_currency_id', $base);
            });
        })->active()->first();

        if(!$market) {
            return response()->json(['success' => false, 'message' => __('Pair is not available')]);
        }

        if(!$market->trade_status || !$market->buy_order_status || !$market->buy_order_status) {
            return response()->json(['success' => false, 'message' => __('Pair is not tradable')]);
        }

        $rate = market_get_stats($market->id, 'last');

        if(!$rate) {
            return response()->json(['success' => false, 'message' => __('Not enough liquidity')]);
        }

        if($base == $market->quote_currency_id) {
            $rate = math_divide(1, $rate);
        }

        return response()->json([
            'success' => true,
            'rate' => math_formatter($rate, 8),
            'market' => $market->name,
            'basePrecision' => $market->base_precision,
            'quotePrecision' => $market->quote_precision,
            'marketBase' => $market->base_currency_id
        ]);

    }
}
