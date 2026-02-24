<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Article\ArticleCollection;
use App\Repositories\Article\ArticleRepository;
use Inertia\Inertia;

class InitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articles = new ArticleCollection((new ArticleRepository())->featured());

        return Inertia::render('Dashboard', [
            'articles' => $articles
        ]);
    }


}
