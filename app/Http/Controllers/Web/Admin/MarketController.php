<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Market\MarketStoreRequest;
use App\Http\Requests\Web\Market\MarketFormRequest;
use App\Models\Market\Market;
use App\Models\Market\MarketAdmin;
use App\Services\Market\MarketService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class MarketController extends Controller
{
    /**
     * @var marketService
     */
    protected $marketService;

    /**
     * MarketController Constructor
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
    public function index()
    {
        return Inertia::render('Admin/Markets/Index', [
            'filters' => Request::all(['search', 'trashed']),
            'markets' => $this->marketService->getMarkets(true, true),
        ]);
    }

    /**
     * Create new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Admin/Markets/Form');
    }

    /**
     * Store new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(MarketFormRequest $request)
    {
        $this->marketService->storeMarket();

        return Redirect::route('admin.markets')->with('success', 'Target Exchange Added');
    }

    /**
     * Edit resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(MarketAdmin $market)
    {
        return Inertia::render('Admin/Markets/Form', [
            'isEdit' => true,
            'market' => $this->marketService->getMarket($market->id, true, true),
        ]);
    }

    /**
     * Update resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(MarketFormRequest $request, MarketAdmin $market)
    {
        $this->marketService->updateMarket($market->id);

        return Redirect::route('admin.markets')->with('success', 'Target Exchange updated.');
    }

    /**
     * Destroy resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(MarketAdmin $market)
    {
        $this->marketService->deleteMarket($market->id);

        return Redirect::route('admin.markets');
    }

    /**
     * Restore resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function restore(MarketAdmin $market)
    {
        $this->marketService->restoreMarket($market->id);

        return Redirect::route('admin.markets');
    }
}
