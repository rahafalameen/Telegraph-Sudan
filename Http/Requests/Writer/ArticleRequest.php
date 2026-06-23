<?php

namespace App\Http\Requests\Writer;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isWriter();
    }

    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'excerpt'         => 'nullable|string|max:500',
            'body'            => 'required|string|min:50',
            'category_id'     => 'required|exists:categories,id',
            'featured_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'youtube_url'     => 'nullable|url|regex:/youtube\.com|youtu\.be/',
            'action'          => 'required|in:draft,submit',
        ];
    }
}
