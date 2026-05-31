<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_attempt_id',
        'assessment_question_id',
        'answer',
        'correct_answer',
        'is_correct',
        'score',
        'feedback',
    ];

    protected $casts = [
        'answer' => 'array',
        'correct_answer' => 'array',
        'is_correct' => 'boolean',
        'score' => 'integer',
    ];

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }
}
