<?php

namespace App\Http\Controllers\Student;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController
{
    public function show(Lesson $lesson)
    {
        $section = $lesson->section;
        $course = $section->course;

        if (!$course->isPublished()) {
            abort(404);
        }

        if (!$lesson->isPublished()) {
            abort(404);
        }

        $user = auth()->user();

        // Check enrollment
        $enrollment = $user->enrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            abort(403);
        }

        // Load course with sections and published lessons
        $course->load([
            'sections' => fn($q) => $q->orderBy('order'),
            'sections.lessons' => fn($q) => $q->published()->orderBy('order'),
        ]);

        // Get or create progress record
        $progress = LessonProgress::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        // Update last accessed
        $progress->update(['last_accessed_at' => now()]);

        // Get all user progress for the course
        $userProgress = $user->lessonProgress()
            ->whereIn('lesson_id', $course->lessons()->published()->pluck('lessons.id'))
            ->get();

        return Inertia::render('Student/Lesson', [
            'course' => $course,
            'lesson' => $lesson->load('attachments'),
            'attachments' => $lesson->attachments,
            'progress' => $userProgress,
        ]);
    }

    public function markComplete(Lesson $lesson, Request $request)
    {
        $user = auth()->user();

        $enrollment = $user->enrollments()
            ->where('course_id', $lesson->section->course_id)
            ->firstOrFail();

        $progress = LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'is_completed' => $request->input('completed', false),
                'completed_at' => $request->input('completed') ? now() : null,
            ]
        );

        // Update course enrollment progress
        $course = $lesson->section->course;
        $totalPublished = $course->lessons()
            ->published()
            ->count();

        $completed = $user->lessonProgress()
            ->whereIn('lesson_id', $course->lessons()->published()->pluck('lessons.id'))
            ->where('is_completed', true)
            ->count();

        $enrollment->update([
            'progress_percentage' => $totalPublished > 0 
                ? intval(($completed / $totalPublished) * 100)
                : 0,
        ]);

        return response()->json(['success' => true, 'progress' => $progress]);
    }

    public function trackWatch(Lesson $lesson, Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'watched_seconds' => 'required|integer|min:0',
        ]);

        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'watched_seconds' => $request->input('watched_seconds'),
                'last_accessed_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
