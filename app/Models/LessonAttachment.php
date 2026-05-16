<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LessonAttachment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (LessonAttachment $attachment): void {
            if ($attachment->file_path !== '') {
                Storage::disk('local')->delete($attachment->file_path);
            }
        });
    }

    public const SAFE_EXTENSIONS = [
        'pdf',
        'zip',
        'txt',
        'md',
        'doc',
        'docx',
        'xlsx',
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    protected $fillable = [
        'lesson_id',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
