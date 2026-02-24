<?php

namespace App\Services\Article;

use App\Repositories\Article\ArticleRepository;

class ArticleService {

    private $articleRepository;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
    }

    public function getArticles($paginate = true, $dashboard = false) {
        return $this->articleRepository->all($paginate, $dashboard);
    }

    public function getArticle($id, $trashed, $dashboard = false) {
        return $this->articleRepository->get($id, $trashed, $dashboard);
    }

    public function storeArticle() {
        return $this->articleRepository->store(request()->all());
    }

    public function updateArticle($id) {
        return $this->articleRepository->update(
            $id,
            request()->all()
        );
    }

    public function deleteArticle($id) {
        return $this->articleRepository->delete($id);
    }

    public function restoreArticle($id) {
        return $this->articleRepository->restore($id);
    }
}
