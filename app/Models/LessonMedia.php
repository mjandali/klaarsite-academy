<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LessonMedia extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (LessonMedia $media): void {
            if ($media->path !== '') {
                Storage::disk($media->disk)->delete($media->path);
            }
        });
    }

    protected $fillable = [
        'lesson_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt_text',
    ];

    protected $appends = [
        'display_url',
    ];

    protected function displayUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->id ? '/lesson-media/'.$this->id : null,
        );
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
