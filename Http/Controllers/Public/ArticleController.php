<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleController extends Controller
{
    public function show(string $slug)
    {
        $article = Article::published()
            ->with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views asynchronously (or via queue in production)
        $article->incrementViews();

        $related = Article::published()
            ->byCategory($article->category_id)
            ->where('id', '!=', $article->id)
            ->with(['author'])
            ->latest()
            ->take(4)
            ->get();

        return view('public.article.show', compact('article', 'related'));
    }
}
