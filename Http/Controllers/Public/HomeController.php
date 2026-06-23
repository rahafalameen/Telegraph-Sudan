<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featured  = Article::published()->featured()->with(['author', 'category'])->latest()->take(5)->get();
        $latest    = Article::published()->with(['author', 'category'])->latest()->take(12)->get();
        $trending  = Article::published()->trending()->with(['author', 'category'])->orderByDesc('views_count')->take(6)->get();
        $categories = Category::active()->withCount('publishedArticles')->get();

        // Latest per category (for category nav sections)
        $byCategory = $categories->mapWithKeys(fn($cat) => [
            $cat->slug => Article::published()->byCategory($cat->id)->with(['author'])->latest()->take(4)->get()
        ]);

        return view('public.home.index', compact(
            'featured', 'latest', 'trending', 'categories', 'byCategory'
        ));
    }
}
