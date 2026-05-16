<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAttachment extends Model
{
    use HasFactory;

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
