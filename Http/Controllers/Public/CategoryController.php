<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $category = Category::active()->where('slug', $slug)->firstOrFail();

        $articles = Article::published()
            ->byCategory($category->id)
            ->with(['author'])
            ->latest()
            ->paginate(16);

        $categories = Category::active()->get();

        return view('public.category.show', compact('category', 'articles', 'categories'));
    }
}
