<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Resources\Article\ArticleResource;
use App\Services\Article\ShowArticleService;

class ShowArticleController extends Controller
{
    protected $showArticleService;

    public function __construct(ShowArticleService $showArticleService)
    {
        $this->showArticleService = $showArticleService;
    }

    public function getAllDoctorArticles()
    {
        $articles = $this->showArticleService->getAllDoctorArticles();
        return ArticleResource::collection($articles);
    }

    public function getLatestDoctorArticle()
    {
        $latestArticle = $this->showArticleService->getLatestDoctorArticle();
        return new ArticleResource($latestArticle);
    }
}
