<?php

namespace App\Http\Requests\Api\Order\Rules;

use App\Models\Market;
use App\Repositories\Market\MarketRepository;
use Illuminate\Contracts\Validation\Rule;

class OrderTickerRule implements Rule
{
    private $isBuyMarket;
    private $tickSize;

    public function __construct($isBuyMarket)
    {
        $this->isBuyMarket = $isBuyMarket;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($this->isBuyMarket && $attribute != "quoteQuantity") return true;

        $isSellMarket = order_is_sell_market(request()->get('type'), request()->get('side'));

        if($isSellMarket && $attribute == "price") return true;

        $marketRepository = new MarketRepository();

        $market = $marketRepository->get(request()->get('market'));

        if($this->isBuyMarket || $attribute == "price") {
            $this->tickSize = $market->quote_ticker_size;
        } else {
            $this->tickSize = $market->base_ticker_size;
        }

        if(!$value || !is_numeric($value) || (mb_strpos('e', (string)$value) !== false) || (mb_strpos('E', (string)$value) !== false) || math_compare($value, 0) < 1) {
            return false;
        }



        $fraction = math_divide($value, $this->tickSize, MATH_SCALE_REMAINDER);

        return $fraction == intval($fraction);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Minimum allowed tick size is') . ' ' . $this->tickSize;
    }
}
