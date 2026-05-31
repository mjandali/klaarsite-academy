<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    public const TYPE_SINGLE_CHOICE = 'single_choice';
    public const TYPE_TRUE_FALSE = 'true_false';
    public const TYPE_MULTIPLE_SELECT = 'multiple_select';
    public const TYPE_ORDERING = 'ordering';
    public const TYPE_MATCHING = 'matching';
    public const TYPE_FILL_BLANK = 'fill_blank';
    public const TYPE_CODE_CHOICE = 'code_choice';

    public const ASSESSMENT_LESSON = 'lesson';
    public const ASSESSMENT_FINAL_EXAM = 'final_exam';

    public const QUESTION_TYPES = [
        self::TYPE_SINGLE_CHOICE,
        self::TYPE_TRUE_FALSE,
        self::TYPE_MULTIPLE_SELECT,
        self::TYPE_ORDERING,
        self::TYPE_MATCHING,
        self::TYPE_FILL_BLANK,
        self::TYPE_CODE_CHOICE,
    ];

    protected $fillable = [
        'course_id',
        'lesson_id',
        'assessment_type',
        'question_type',
        'question',
        'explanation',
        'points',
        'order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function options()
    {
        return $this->hasMany(AssessmentQuestionOption::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLessonAssessment($query, Lesson $lesson)
    {
        return $query
            ->where('assessment_type', self::ASSESSMENT_LESSON)
            ->where('lesson_id', $lesson->id);
    }

    public function scopeForFinalExam($query, Course $course)
    {
        return $query
            ->where('assessment_type', self::ASSESSMENT_FINAL_EXAM)
            ->where('course_id', $course->id)
            ->whereNull('lesson_id');
    }
}
