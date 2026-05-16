<?php

namespace App\Policies;

use App\Models\LessonMedia;
use App\Models\User;

class LessonMediaPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, LessonMedia $media): bool
    {
        $lesson = $media->lesson()->with('section.course')->first();

        return $lesson !== null
            && $lesson->isPublished()
            && $lesson->section?->course?->isPublished()
            && $user->enrollments()->where('course_id', $lesson->section->course_id)->exists();
    }
}
