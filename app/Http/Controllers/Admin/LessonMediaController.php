<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Support\WebpImageStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LessonMediaController extends Controller
{
    public function store(Request $request, Lesson $lesson): JsonResponse
    {
        $lesson->loadMissing('section.course');
        $this->authorize('update', $lesson->section->course);

        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $stored = app(WebpImageStore::class)->storeWithMetadata(
                $request->file('image'),
                'lesson-media/'.$lesson->id,
                'local',
            );
        } catch (\RuntimeException $exception) {
            throw ValidationException::withMessages([
                'image' => $exception->getMessage(),
            ]);
        }

        $media = $lesson->media()->create([
            'disk' => 'local',
            'path' => $stored['path'],
            'original_name' => $request->file('image')->getClientOriginalName(),
            'mime_type' => $stored['mime_type'],
            'size' => $stored['size'],
            'width' => $stored['width'],
            'height' => $stored['height'],
            'alt_text' => $data['alt_text'] ?? null,
        ]);

        return response()->json([
            'id' => $media->id,
            'display_url' => $media->display_url,
            'original_name' => $media->original_name,
            'width' => $media->width,
            'height' => $media->height,
        ], 201);
    }
}
