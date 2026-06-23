<?php

namespace App\Http\Controllers\Writer;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'total'     => $user->articles()->count(),
            'drafts'    => $user->articles()->where('status', ArticleStatus::DRAFT)->count(),
            'pending'   => $user->articles()->where('status', ArticleStatus::PENDING)->count(),
            'published' => $user->articles()->where('status', ArticleStatus::PUBLISHED)->count(),
            'rejected'  => $user->articles()->where('status', ArticleStatus::REJECTED)->count(),
        ];

        $recentArticles = $user->articles()->with('category')->latest()->take(10)->get();

        return view('writer.dashboard.index', compact('stats', 'recentArticles'));
    }
}
