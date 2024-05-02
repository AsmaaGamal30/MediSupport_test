<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ShowArticleController extends Controller
{
    public function getAllDoctorArticles()
    {
        $doctorId = auth()->guard('doctor')->id();
        $articles = Article::where('doctor_id', $doctorId)->paginate(10);
        return ArticleResource::collection($articles);
    }


    public function getLatestDoctorArticle()
    {
        $doctorId = auth()->guard('doctor')->id();
        $latestArticle = Article::where('doctor_id', $doctorId)->latest()->first();
        return new ArticleResource($latestArticle);
    }
}
