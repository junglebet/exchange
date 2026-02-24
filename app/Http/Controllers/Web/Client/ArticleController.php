<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Article\ArticleCollection;
use App\Http\Resources\Article\Article as ArticleResource;
use App\Models\Article\Article;
use App\Repositories\Article\ArticleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Inertia;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sort = "all")
    {
        $articles = new ArticleCollection((new ArticleRepository())->all(true));

        return Inertia::render('Article/Articles', [
            'articles' => $articles,
            'sort' => $sort
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        $article = (new ArticleRepository())->get($article->id);

        if(!$article) {
            throw new ModelNotFoundException();
        }

        return Inertia::render('Article/Article', [
            'article' => new ArticleResource($article),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showShort(Article $article)
    {
        $article = (new ArticleRepository())->get($article->id);

        if(!$article) {
            throw new ModelNotFoundException();
        }

        return Inertia::render('Article/ArticleShort', [
            'article' => new ArticleResource($article),
        ]);
    }

    public function featured() {

        $articles = new ArticleCollection((new ArticleRepository())->featured());

        return response()->json($articles);
    }
}
