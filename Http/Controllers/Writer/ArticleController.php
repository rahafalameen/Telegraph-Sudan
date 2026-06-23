<?php

namespace App\Http\Controllers\Writer;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private ArticleService $articleService) {}

    public function index(): View
    {
        $articles = auth()->user()->articles()->with('category')->latest()->paginate(15);
        return view('writer.articles.index', compact('articles'));
    }

    public function create(): View
    {
        $categories = Category::active()->get();
        return view('writer.articles.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'excerpt'        => 'nullable|string|max:500',
            'body'           => 'required|string',
            'category_id'    => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:5120',
            'youtube_url'    => 'nullable|url',
            'action'         => 'required|in:draft,submit',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image_file'] = $request->file('featured_image');
        }

        $request->action === 'draft'
            ? $this->articleService->saveDraft($data)
            : $this->articleService->submitForReview($data);

        $msg = $request->action === 'draft' ? 'تم حفظ المسودة.' : 'تم إرسال المقال للمراجعة.';
        return redirect()->route('writer.articles.index')->with('success', $msg);
    }

    public function edit(Article $article): View
    {
        abort_unless($article->author_id === auth()->id() && !$article->isPublished(), 403);
        $categories = Category::active()->get();
        return view('writer.articles.create', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        abort_unless($article->author_id === auth()->id() && !$article->isPublished(), 403);

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'excerpt'        => 'nullable|string|max:500',
            'body'           => 'required|string',
            'category_id'    => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:5120',
            'youtube_url'    => 'nullable|url',
            'action'         => 'required|in:draft,submit',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image_file'] = $request->file('featured_image');
        }

        $request->action === 'draft'
            ? $this->articleService->saveDraft($data, $article)
            : $this->articleService->submitForReview($data, $article);

        return redirect()->route('writer.articles.index')->with('success', 'تم تحديث المقال.');
    }

    public function destroy(Article $article): RedirectResponse
{
    abort_unless($article->author_id === auth()->id(), 403);
    $article->delete();
    return redirect()->route('writer.articles.index')
        ->with('success', 'تم حذف المقال.');
}
}
