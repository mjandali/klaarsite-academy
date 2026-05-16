<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\VideoEmbedParser;
use Illuminate\Http\Request;
use InvalidArgumentException;

class LessonController extends Controller
{
    public function parseVideo(Request $request)
    {
        $request->validate([
            'url' => 'required|string|max:2048',
        ]);

        try {
            $data = VideoEmbedParser::normalize($request->input('url'));
            return response()->json($data);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
