<?php

namespace App\Policies;

use App\Models\LessonAttachment;
use App\Models\User;

class LessonAttachmentPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->isAdmin() ? true : null;
    }

    public function download(User $user, LessonAttachment $attachment): bool
    {
        $courseId = $attachment->lesson()->with('section')->first()?->section?->course_id;

        return $courseId !== null && $user->enrollments()->where('course_id', $courseId)->exists();
    }

    public function update(User $user, LessonAttachment $attachment): bool
    {
        return false;
    }

    public function delete(User $user, LessonAttachment $attachment): bool
    {
        return false;
    }
}
