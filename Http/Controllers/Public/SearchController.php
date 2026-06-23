<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $searchService) {}

    public function index(Request $request)
    {
        $request->validate([
            'q'          => 'nullable|string|max:200',
            'category'   => 'nullable|integer|exists:categories,id',
            'date'       => 'nullable|date',
        ]);

        $results = $this->searchService->search(
            keyword:    $request->q,
            categoryId: $request->category,
            date:       $request->date,
        );

        $categories = Category::active()->get();

        return view('public.search.index', compact('results', 'categories'));
    }

    /**
     * JSON endpoint for live search suggestions.
     */
    public function suggestions(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        return response()->json(
            $this->searchService->suggestions($request->q)
        );
    }
}
