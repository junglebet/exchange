<?php

namespace App\Http\Requests\Web\Market;

use App\Http\Requests\Web\Market\Rules\MarketStoreRule;
use Illuminate\Foundation\Http\FormRequest;
use Auth;

class MarketFormRequest extends FormRequest
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
        return [
            'id' => ['sometimes', 'required', 'exists:markets'],
            'name' => ['required', 'max:30', 'min:3'],
            'base_currency_id' => ['bail', 'required', 'numeric', 'exists:currencies,id'],
            'quote_currency_id' => ['bail', 'required', 'numeric', 'exists:currencies,id', new MarketStoreRule()],
            'base_precision' => ['required', 'numeric', 'min:0', 'max:18'],
            'quote_precision' => ['required', 'numeric', 'min:0', 'max:18'],
            'min_trade_size' => ['required', 'numeric', 'min:0.000000000001', 'max:900000000000000000'],
            'max_trade_size' => ['required', 'numeric', 'min:0.000000000001', 'max:900000000000000000'],
            'min_trade_value' => ['required', 'numeric', 'min:0.000000000001', 'max:900000000000000000'],
            'max_trade_value' => ['required', 'numeric', 'min:0.000000000001', 'max:900000000000000000'],
            'base_ticker_size' => ['required', 'numeric', 'min:0.000000000001', 'max:900000000000000000'],
            'quote_ticker_size' => ['required', 'numeric', 'min:0.000000000001', 'max:900000000000000000'],
            'status' => ['required'],
            'trade_status' => ['required', 'boolean'],
            'buy_order_status' => ['required', 'boolean'],
            'sell_order_status' => ['required', 'boolean'],
            'cancel_order_status' => ['required', 'boolean'],
            'is_tradingview' => ['string', 'max:255'],
            'custom_market_path' => ['max:255'],
            'last' => ['required', 'numeric','min:0.0000000001', 'max:900000000000000000'],
            'discount' => ['required', 'numeric','min:0', 'max:100'],
            'discount_bid' => ['required', 'numeric','min:0', 'max:100'],
        ];
    }
}
