<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleRequests\CreateArticleRequest;
use App\Http\Requests\ArticleRequests\UpdateArticleRequest;
use App\Http\Resources\Article\ArticleResource;
use App\Models\Article;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;


class ArticleController extends Controller
{
    use ApiResponse;


    public function index()
    {
        return ArticleResource::collection(Article::query()->paginate(10));
    }

    public function show($id)
    {
        $article = Article::findOrFail($id);
        return new ArticleResource($article);
    }

    public function store(CreateArticleRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->error($validator->errors()->toJson(), 400);
        }
        $doctorId = auth()->guard('doctor')->id();

        $article = Article::create(array_merge(
            $request->validated(),
            [
                'doctor_id' => $doctorId,
                'image' => $request->file('image')->store('images'),

            ]
        ));
        return $this->sendData('Doctor Article created successfully, do you want to create one more ?', new ArticleResource($article));
    }


    public function update(UpdateArticleRequest $request, $id)
    {
        $article = Article::findOrFail($id);

        if (auth()->guard('doctor')->id() !== $article->doctor_id) {
            return $this->error('You are not authorized to update this article.', 403);
        }

        $article->update([
            'title' => $request->input('title'),
            'body' => $request->input('body'),
        ]);

        if ($request->hasFile('image')) {
            $oldImagePath = $article->image;
            $imagePath = $request->file('image')->store('articles');
            $article->update(['image' => $imagePath]);
            if ($oldImagePath) {
                Storage::delete($oldImagePath);
            }
        }
        return $this->sendData('Article updated successfully.', new ArticleResource($article));
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        if (auth()->guard('doctor')->id() !== $article->doctor_id && !auth()->guard('admin')->id()) {
            return $this->error('You are not authorized to delete this article.', 403);
        }

        if ($article->image) {
            Storage::delete($article->image);
        }

        $article->delete();

        //return response()->json(['message' => 'Article deleted successfully']);
        return $this->success('Article deleted successfully');
    }
}