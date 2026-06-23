<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            
            $stats = [
                'total_articles' => Article::count(),
                'published'      => Article::where('status', 'published')->count(),
                'pending'        => Article::where('status', 'pending')->count(),
                'total_writers'  => User::where('role', 'writer')->count(),
                'total_views'    => Schema::hasColumn('articles', 'views') ? Article::sum('views') : 0,
            ];

            $view->with([
                'stats'           => $stats,
                'pendingArticles' => Article::where('status', 'pending')->latest()->take(5)->get(),
                'topArticles'     => Schema::hasColumn('articles', 'views') ? Article::orderByDesc('views')->take(5)->get() : collect([]),
                'recentLogs'      => collect([]),
                'categories'      => Category::all(),
            ]);
        });
    }
}