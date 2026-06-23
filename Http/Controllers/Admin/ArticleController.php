<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Http\Requests\Admin\RejectArticleRequest;
use App\Http\Requests\Admin\ScheduleArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon; // 💡 تم نقل الاستدعاء هنا في مكانه الصحيح والآمن

class ArticleController extends Controller
{
    public function __construct(private ArticleService $articleService) {}

    public function index(Request $request): View
    {
        $articles = Article::query()
            ->with(['author', 'category'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->byCategory($request->category))
            ->when($request->search, fn($q) => $q->search($request->search))
            ->latest()
            ->paginate(20);

        $categories = Category::active()->get();
        $statuses   = ArticleStatus::cases();

        return view('admin.articles.index', compact('articles', 'categories', 'statuses'));
    }

    public function create(): View
    {
        $categories = Category::active()->get();
        return view('admin.articles.create', compact('categories'));
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
            'is_featured'    => 'boolean',
            'is_trending'    => 'boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image_file'] = $request->file('featured_image');
        }

        $data['author_id'] = auth()->id();
        $data['status']    = ArticleStatus::PUBLISHED;
        $data['published_at'] = now();

        $article = $this->articleService->saveDraft($data);

        return redirect()->route('admin.articles.index')
            ->with('success', 'تم إنشاء المقال بنجاح.');
    }

    public function show(Article $article): View
    {
        $article->load(['author', 'category', 'tags']);
        return view('admin.articles.show', compact('article'));
    }

    public function edit(Article $article): View
    {
        $categories = Category::active()->get();
        return view('admin.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'excerpt'        => 'nullable|string|max:500',
            'body'           => 'required|string',
            'category_id'    => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:5120',
            'youtube_url'    => 'nullable|url',
            'is_featured'    => 'boolean',
            'is_trending'    => 'boolean',
        ]);

        if ($request->hasFile('featured_image')) {
            $data['featured_image_file'] = $request->file('featured_image');
        }

        $this->articleService->saveDraft($data, $article);

        return redirect()->route('admin.articles.index')
            ->with('success', 'تم تحديث المقال.');
    }

    public function approve(Article $article)
    {
        $article->update([
            'status' => 'published',
            'published_at' => Carbon::now(), 
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'تم الموافقة على المقال ونشره بنجاح.');
    }

    public function reject(Article $article)
    {
        $article->update([
            'status' => 'draft', 
            'published_at' => null,
        ]);

        return redirect()->route('admin.articles.index')->with('info', 'تم رفض المقال وإعادته للمسودات.');
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->articleService->publish($article);
        return back()->with('success', 'تم نشر المقال.');
    }

    public function schedule(Request $request, Article $article): RedirectResponse
    {
        $request->validate(['scheduled_at' => 'required|date|after:now']);
        $this->articleService->schedule($article, $request->scheduled_at);
        return back()->with('success', 'تم جدولة المقال.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', 'تم حذف المقال.');
    }
}