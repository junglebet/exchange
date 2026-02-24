<?php

namespace App\Http\Requests\Api\Order;

use App\Http\Requests\Api\Order\Rules\OrderQuantityRule;
use App\Http\Requests\Api\Order\Rules\OrderMarketRule;
use App\Http\Requests\Api\Order\Rules\OrderPriceRule;
use App\Http\Requests\Api\Order\Rules\OrderQuoteQuantityRule;
use App\Http\Requests\Api\Order\Rules\OrderSideRule;
use App\Http\Requests\Api\Order\Rules\OrderTickerRule;
use App\Http\Requests\Api\Order\Rules\OrderTriggerConditionRule;
use App\Http\Requests\Api\Order\Rules\OrderTriggerPriceRule;
use App\Http\Requests\Api\Order\Rules\OrderTypeRule;
use App\Services\Market\MarketService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\RequiredIf;
use Auth;

class OrderStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isMarket = order_is_market($this->get('type'));
        $isBuyMarket = order_is_buy_market($this->get('type'), $this->get('side'));
        $isStopLimit = order_is_stop_limit($this->get('type'));

        //$running = Cache::get('markets_liquidity.active', []);
        //if ($running && isset($running[request()->get('market')])) {
        //    (new MarketService())->parseLiquidity(request()->get('market'));
        //}

        return [
            'market' => ['bail', 'required', new OrderMarketRule()],
            'type' => ['bail', 'required', new OrderTypeRule()],
            'side' => ['bail', 'required', new OrderSideRule()],
            'quantity' => ['bail', new RequiredIf(!$isBuyMarket), new OrderTickerRule($isBuyMarket), new OrderQuantityRule()],
            'price' => ['bail', new RequiredIf(!$isMarket), new OrderTickerRule($isBuyMarket), new OrderPriceRule()],
            'quoteQuantity' => ['bail', new RequiredIf($isBuyMarket), new OrderTickerRule($isBuyMarket), new OrderQuoteQuantityRule()],
            'trigger_price' => ['bail', new RequiredIf($isStopLimit), new OrderTriggerPriceRule()],
            //'trigger_condition' => ['bail', new RequiredIf($isStopLimit), new OrderTriggerConditionRule()],
            //'trigger_condition' => ['bail', new RequiredIf($isStopLimit)],
        ];
    }
}
