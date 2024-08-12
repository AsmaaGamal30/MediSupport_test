<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequests\CreateArticleRequest;
use App\Http\Requests\ArticleRequests\UpdateArticleRequest;
use App\Http\Resources\Article\ArticleResource;
use App\Services\Article\ArticleService;
use App\Traits\ApiResponse;

class ArticleController extends Controller
{
    use ApiResponse;

    protected $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    public function index()
    {
        $articles = $this->articleService->getAllArticles();
        return ArticleResource::collection($articles);
    }

    public function show($id)
    {
        $article = $this->articleService->getArticleById($id);
        return new ArticleResource($article);
    }

    public function store(CreateArticleRequest $request)
    {
        $result = $this->articleService->createArticle($request);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->sendData('Doctor Article created successfully, do you want to create one more ?', new ArticleResource($result));
    }

    public function update(UpdateArticleRequest $request, $id)
    {
        $result = $this->articleService->updateArticle($request, $id);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->sendData('Article updated successfully.', new ArticleResource($result));
    }

    public function destroy($id)
    {
        $result = $this->articleService->deleteArticle($id);
        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code']);
        }
        return $this->success('Article deleted successfully');
    }
}
