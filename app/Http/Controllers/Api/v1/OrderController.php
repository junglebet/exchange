<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\OrderCancelRequest;
use App\Http\Requests\Api\Order\OrderStoreRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Interfaces\Order\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * OrdersController constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->middleware(['auth:sanctum','maintenance'])->only(['store','cancel', 'open']);

        $this->orderRepository = $orderRepository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\Api\Order\OrderStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderStoreRequest $request)
    {
        if(!$request->user()->tokenCan('trade')) {
            return response()->json(['message' => 'Unauthorized'], STATUS_FORBIDDEN);
        }

        try {
            return response()->json(['message' => $this->orderRepository->store()]);
        } catch (\Exception $e) {
            Log::error($e);
            exit();
        }
    }

    /**
     * Cancel open order
     *
     * @param  App\Http\Requests\Api\Order\OrderCancelRequest $request
     * @return \Illuminate\Http\Response
     */
    public function cancel(OrderCancelRequest $request)
    {
        if(!$request->user()->tokenCan('trade')) {
            return response()->json(['message' => 'Unauthorized'], STATUS_FORBIDDEN);
        }

        $status = $this->orderRepository->cancel();

        return response()->json(['message' => $status ? 'request processed' : 'request_was_not_processed']);
    }

    /**
     * Open orders
     *
     * @param  Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function open(Request $request)
    {
        $market = request()->get('market', null);

        return new OrderCollection($this->orderRepository->open($market));
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
