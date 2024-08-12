<?php

namespace App\Services\Article;

use App\Models\Article;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;

class ArticleService
{
    public function getAllArticles()
    {
        return Article::query()->paginate(10);
    }

    public function getArticleById($id)
    {
        return Article::findOrFail($id);
    }

    public function createArticle($request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return ['error' => $validator->errors()->toJson(), 'code' => 400];
        }
        $doctorId = auth()->guard('doctor')->id();

        $article = Article::create(array_merge(
            $request->validated(),
            [
                'doctor_id' => $doctorId,
                'image' => $request->file('image')->store('articles', 'public'),
            ]
        ));

        return $article;
    }

    public function updateArticle($request, $id)
    {
        $article = Article::findOrFail($id);

        if (auth()->guard('doctor')->id() !== $article->doctor_id) {
            return ['error' => 'You are not authorized to update this article.', 'code' => 403];
        }

        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $imageFile = $request->file('image');
            $imagePath = $imageFile->store('articles', 'public');
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $validatedData['image'] = $imagePath;
        }

        $article->update($validatedData);

        return $article;
    }

    public function deleteArticle($id)
    {
        $article = Article::findOrFail($id);

        if (auth()->guard('doctor')->id() !== $article->doctor_id && !auth()->guard('admin')->id()) {
            return ['error' => 'You are not authorized to delete this article.', 'code' => 403];
        }

        if ($article->image) {
            Storage::delete($article->image);
        }

        $article->delete();

        return true;
    }
}
