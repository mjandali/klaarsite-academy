<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['text', 'video', 'mixed'])],
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:2048',
            'duration_minutes' => 'nullable|integer|min:1|max:10000',
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
