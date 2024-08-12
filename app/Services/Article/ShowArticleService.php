<?php

namespace App\Services\Article;

use App\Models\Article;

class ShowArticleService
{
    public function getAllDoctorArticles()
    {
        $doctorId = auth()->guard('doctor')->id();
        return Article::where('doctor_id', $doctorId)->paginate(10);
    }

    public function getLatestDoctorArticle()
    {
        $doctorId = auth()->guard('doctor')->id();
        return Article::where('doctor_id', $doctorId)->latest()->first();
    }
}
