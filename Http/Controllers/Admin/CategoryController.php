<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * عرض قائمة التصنيفات مع حساب عدد المقالات التابعة لها.
     */
    public function index(): View
    {
        // تم جلب العداد بالعلاقة الصحيحة (articles) والترتيب المعتمد
        $categories = Category::withCount('articles')->orderBy('sort_order')->get();
        
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * حفظ تصنيف جديد في قاعدة البيانات.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:categories',
            'name_ar' => 'required|string|max:100',
            'color'   => 'nullable|string|max:7',
            'icon'    => 'nullable|string|max:50',
        ]);

        Category::create($request->all());

        return redirect()->route('admin.categories.index')
            ->with('success', __('admin.category_created'));
    }

    /**
     * تحديث بيانات تصنيف موجود مسبقاً.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100|unique:categories,name,' . $category->id,
            'name_ar' => 'required|string|max:100',
            'color'   => 'nullable|string|max:7',
        ]);

        $category->update($request->all());

        return back()->with('success', __('admin.category_updated'));
    }

    /**
     * حذف التصنيف نهائياً بشرط ألا يحتوي على مقالات مرتبطة.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // التحقق هندسياً من عدم وجود مقالات تابعة للتصنيف قبل الحذف لحماية البيانات
        abort_if($category->articles()->exists(), 422, __('admin.category_has_articles'));
        
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', __('admin.category_deleted'));
    }
}