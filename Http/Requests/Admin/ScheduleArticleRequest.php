<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleArticleRequest extends FormRequest
{
    public function authorize(): bool { return auth()->user()->canPublish(); }

    public function rules(): array
    {
        return ['scheduled_at' => 'required|date|after:now'];
    }
}
