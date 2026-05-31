<?php

namespace App\Models;

use App\Support\RichTextSanitizer;
use App\Support\VideoEmbedParser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Lesson $lesson): void {
            $lesson->loadMissing('attachments', 'media');
            $lesson->attachments->each->delete();
            $lesson->media->each->delete();
        });
    }

    protected $fillable = [
        'course_section_id',
        'title',
        'description',
        'type',
        'content',
        'video_url',
        'video_provider',
        'video_id',
        'duration_minutes',
        'order',
        'status',
    ];

    protected $appends = [
        'video_embed_url',
    ];

    protected $casts = [
    ];

    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => RichTextSanitizer::sanitize($value),
        );
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => RichTextSanitizer::sanitize($value),
        );
    }

    protected function videoEmbedUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => VideoEmbedParser::embedUrl($this->video_provider, $this->video_id),
        );
    }

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function attachments()
    {
        return $this->hasMany(LessonAttachment::class);
    }

    public function media()
    {
        return $this->hasMany(LessonMedia::class);
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function assessmentQuestions()
    {
        return $this->hasMany(AssessmentQuestion::class)->orderBy('order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
