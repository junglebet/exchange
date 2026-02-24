<?php

namespace App\Http\Requests\Api\Order\Rules;

use App\Models\Order\FuturesContract;
use App\Repositories\Order\OrderRepository;
use Illuminate\Contracts\Validation\Rule;
use Auth;

class OrderFuturesCancelRule implements Rule
{
    /**
     * @var OrderRepository
     */
    private $orderRepository, $error;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  string $uuid
     * @return bool
     */
    public function passes($attribute, $uuid)
    {
        if(!$order = FuturesContract::find($uuid)) {
            $this->error = 'Invalid order';
            return false;
        }

        if(!market_is_cancel_orders_allowed($order->market)) {
            $this->error = 'Cancelling orders are not allowed';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __($this->error);
    }
}
