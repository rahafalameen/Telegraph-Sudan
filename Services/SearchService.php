<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchService
{
    /**
     * Search published articles by keyword, category, and/or date.
     */
    public function search(
        ?string $keyword = null,
        ?int $categoryId = null,
        ?string $date = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Article::query()->published()->with(['author', 'category']);

        if ($keyword) {
            $query->search($keyword);
        }

        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        if ($date) {
            $query->whereDate('published_at', $date);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Get search suggestions (used for live search dropdowns).
     */
    public function suggestions(string $keyword, int $limit = 5): array
    {
        return Article::published()
            ->search($keyword)
            ->select('id', 'title', 'slug', 'featured_image')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
