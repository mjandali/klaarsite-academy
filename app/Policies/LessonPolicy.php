<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->isAdmin() ? true : null;
    }

    public function view(User $user, Lesson $lesson): bool
    {
        if (! $lesson->isPublished()) {
            return false;
        }

        $courseId = $lesson->section()->value('course_id');

        return $courseId !== null && $user->enrollments()->where('course_id', $courseId)->exists();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return false;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
