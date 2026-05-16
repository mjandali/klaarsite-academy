<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'course_section_id' => 'required|exists:course_sections,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['text', 'video', 'mixed'])],
            'content' => 'nullable|string',
            'video_url' => 'nullable|string|max:2048',
            'duration_minutes' => 'nullable|integer|min:1|max:10000',
            'order' => 'nullable|integer|min:1',
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
