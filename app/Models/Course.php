<?php

namespace App\Models;

use App\Support\RichTextSanitizer;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Course $course): void {
            $course->loadMissing('sections.lessons.attachments', 'sections.lessons.media');

            $course->sections
                ->flatMap(fn ($section) => $section->lessons)
                ->flatMap(fn ($lesson) => $lesson->attachments)
                ->each
                ->delete();

            $course->sections
                ->flatMap(fn ($section) => $section->lessons)
                ->flatMap(fn ($lesson) => $lesson->media)
                ->each
                ->delete();
        });
    }

    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'description',
        'thumbnail_url',
        'price',
        'currency',
        'level',
        'duration_hours',
        'slug',
        'meta_description',
        'course_format',
        'status',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => RichTextSanitizer::sanitize($value),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class)->orderBy('order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, CourseSection::class)->orderBy('lessons.order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function assessmentQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('order');
    }

    public function finalExamQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class)
            ->where('assessment_type', AssessmentQuestion::ASSESSMENT_FINAL_EXAM)
            ->whereNull('lesson_id')
            ->orderBy('order');
    }

    public function assessmentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function scopeFree($query)
    {
        return $query->where('price', 0);
    }

    public function scopePaid($query)
    {
        return $query->where('price', '>', 0);
    }
}
