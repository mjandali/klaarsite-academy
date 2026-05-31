<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_question_id',
        'label',
        'value',
        'is_correct',
        'correct_order',
        'match_value',
        'order',
        'settings',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'correct_order' => 'integer',
        'order' => 'integer',
        'settings' => 'array',
    ];

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }
}
