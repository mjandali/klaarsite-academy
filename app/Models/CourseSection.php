<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (CourseSection $section): void {
            $section->loadMissing('lessons.attachments', 'lessons.media');

            $section->lessons
                ->flatMap(fn ($lesson) => $lesson->attachments)
                ->each
                ->delete();

            $section->lessons
                ->flatMap(fn ($lesson) => $lesson->media)
                ->each
                ->delete();
        });
    }

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'order',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
