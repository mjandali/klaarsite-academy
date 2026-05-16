<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', Rule::unique('courses')->ignore($this->course)],
            'subtitle' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0|max:9999.99',
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'duration_hours' => 'required|integer|min:1|max:999',
            'course_format' => ['required', Rule::in(['text', 'video', 'mixed'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'thumbnail_url' => 'nullable|url',
            'meta_description' => 'nullable|string|max:255',
        ];
    }
}
