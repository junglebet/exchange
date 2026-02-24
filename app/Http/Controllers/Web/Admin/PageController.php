<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Currency\CurrencyFormRequest;
use App\Http\Requests\Web\Page\PageFormRequest;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyAdmin;
use App\Models\Page\Page;
use App\Repositories\Network\NetworkRepository;
use App\Repositories\Page\PageRepository;
use App\Services\Currency\CurrencyService;
use App\Services\PaymentGateways\Coin\Coinpayments\Services\CoinpaymentsService;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Setting;

class PageController extends Controller
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * PageController Constructor
     *
     * @param PageRepository $pageRepository
     *
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = $this->pageRepository->get();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Create new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Admin/Pages/Form');
    }

    /**
     * Store new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(PageFormRequest $request)
    {
        $this->pageRepository->store($request->only([
            'title',
            'slug',
            'content',
            'seo_title',
            'seo_description',
            'seo_keywords',
            'status'
        ]));

        return Redirect::route('admin.pages');
    }

    /**
     * Edit resource.
     *
     * @param Page $page
     * @return \Inertia\Response
     */
    public function edit(Page $page)
    {
        $page = $this->pageRepository->getPageById($page->id);

        return Inertia::render('Admin/Pages/Form', [
            'isEdit' => true,
            'page' => $page,
        ]);
    }

    /**
     * Update resource.
     *
     * @param Page $page
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PageFormRequest $request, Page $page)
    {
        $this->pageRepository->update($page->id, $request->only([
            'title',
            'slug',
            'content',
            'seo_title',
            'seo_description',
            'seo_keywords',
            'status'
        ]));

        return Redirect::route('admin.pages');
    }

    /**
     * Destroy resource.
     *
     * @param Page $page
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Page $page)
    {
        $this->pageRepository->delete($page->id);

        return Redirect::route('admin.pages');
    }
}
