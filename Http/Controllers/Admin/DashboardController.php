<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_articles'    => Article::count(),
            'published'         => Article::where('status', ArticleStatus::PUBLISHED)->count(),
            'pending'           => Article::where('status', ArticleStatus::PENDING)->count(),
            'total_writers'     => User::byRole(\App\Enums\UserRole::WRITER)->count(),
            'total_views'       => Article::sum('views_count'),
        ];

        $pendingArticles = Article::where('status', ArticleStatus::PENDING)
            ->with(['author', 'category'])
            ->latest()
            ->take(10)
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $topArticles = Article::published()
            ->orderByDesc('views_count')
            ->take(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats', 'pendingArticles', 'recentLogs', 'topArticles'
        ));
    }
}
