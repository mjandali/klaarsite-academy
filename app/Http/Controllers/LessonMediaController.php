<?php

namespace App\Http\Controllers;

use App\Models\LessonMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LessonMediaController extends Controller
{
    public function show(Request $request, LessonMedia $media): Response
    {
        $media->loadMissing('lesson.section.course');
        $this->authorize('view', $media);

        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($media->path), 404);

        return response($disk->get($media->path), 200, [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="'.$media->original_name.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
