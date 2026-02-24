<?php

namespace App\Repositories\Article;

use App\Interfaces\Article\ArticleRepositoryInterface;
use App\Models\Article\Article;
use Auth;

class ArticleRepository implements ArticleRepositoryInterface
{
    /**
     * @var Article
     */
    protected $article;

    /**
     * ArticleRepository constructor.
     *
     */
    public function __construct()
    {
        $this->article = new Article();
    }

    public function get($id, $trashed = false, $dashboard = false, $relations = null) {

        $article = Article::whereId($id);

        if($relations === null) {
            $article->with(['file']);
        } else {
            $article->with($relations);
        }

        if(!$dashboard) {
            $article->active();
        }

        if($trashed) {
            $article->withTrashed();
        }

        return $article->first();
    }

    public function all($paginate, $dashboard = false, $relations = ['file'], $type = false) {

        $articles = Article::filter(request()->only(['search', 'trashed']))->orderByLatest();

        if(!$dashboard) {
            $articles->active();
        }

        if($type) {
            $articles->type($type);
        }

        $articles->with($relations);

        if($paginate) {
            return $articles->paginate(24)->withQueryString();
        } else {
            return $articles->get();
        }
    }

    public function featured() {

        $articles = Article::orderByLatest();

        $articles->active();
        $articles->featured();

        return $articles->get();
    }

    public function count() {
        $article = Article::query();
        return $article->count();
    }

    public function store($data) {

        $article = $this->article->create($data);

        return $article->fresh();
    }

    public function update($id, $data) {

        $article = Article::withTrashed()->find($id);
        $article->update($data);

        return $article->fresh();
    }

    public function delete($id) {

        $article = Article::find($id);
        $article->delete();

        return true;
    }

    public function restore($id) {

        $article = Article::withTrashed()->find($id);
        $article->restore();

        return true;
    }

    public function getReport($filters = [], $pagination = true) {

        $article = Article::query();

        $article->filter($filters)->orderBy('title', 'asc');

        if(!$pagination) {
            return $article->get();
        }

        return $article->paginate(150)->withQueryString();
    }
}
