<?php

namespace App\Services;

use App\Models\AssessmentQuestion;
use Illuminate\Support\Collection;

class AssessmentGrader
{
    public function grade(Collection $questions, array $answers): array
    {
        $results = [];
        $score = 0;
        $maxScore = 0;

        foreach ($questions as $question) {
            $rawAnswer = $answers[(string) $question->id] ?? $answers[$question->id] ?? null;
            $result = $this->gradeQuestion($question, $rawAnswer);
            $score += $result['score'];
            $maxScore += (int) $question->points;
            $results[] = $result;
        }

        $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100, 2) : 0;

        return [
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'passed' => $percentage >= 70,
            'answers' => $results,
        ];
    }

    public function gradeQuestion(AssessmentQuestion $question, mixed $rawAnswer): array
    {
        $question->loadMissing('options');
        $points = (int) $question->points;
        $isCorrect = false;
        $normalizedAnswer = $this->normalizeAnswer($rawAnswer);
        $correctAnswer = $this->correctAnswer($question);

        switch ($question->question_type) {
            case AssessmentQuestion::TYPE_SINGLE_CHOICE:
            case AssessmentQuestion::TYPE_TRUE_FALSE:
            case AssessmentQuestion::TYPE_FILL_BLANK:
            case AssessmentQuestion::TYPE_CODE_CHOICE:
                $isCorrect = (string) ($normalizedAnswer['selected_option_id'] ?? '') === (string) ($correctAnswer['selected_option_id'] ?? '');
                break;

            case AssessmentQuestion::TYPE_MULTIPLE_SELECT:
                $given = $this->sortIds($normalizedAnswer['selected_option_ids'] ?? []);
                $correct = $this->sortIds($correctAnswer['selected_option_ids'] ?? []);
                $isCorrect = $given === $correct;
                break;

            case AssessmentQuestion::TYPE_ORDERING:
                $given = array_map('strval', $normalizedAnswer['ordered_option_ids'] ?? []);
                $correct = array_map('strval', $correctAnswer['ordered_option_ids'] ?? []);
                $isCorrect = $given === $correct;
                break;

            case AssessmentQuestion::TYPE_MATCHING:
                $given = $this->normalizeMatching($normalizedAnswer['matches'] ?? []);
                $correct = $this->normalizeMatching($correctAnswer['matches'] ?? []);
                $isCorrect = $given === $correct;
                break;
        }

        return [
            'question_id' => $question->id,
            'answer' => $normalizedAnswer,
            'correct_answer' => $correctAnswer,
            'is_correct' => $isCorrect,
            'score' => $isCorrect ? $points : 0,
            'feedback' => $isCorrect
                ? (app()->getLocale() === 'ar' ? 'إجابة صحيحة.' : 'Correct answer.')
                : (app()->getLocale() === 'ar' ? 'إجابة غير صحيحة.' : 'Incorrect answer.'),
        ];
    }

    public function correctAnswer(AssessmentQuestion $question): array
    {
        $question->loadMissing('options');

        if (in_array($question->question_type, [
            AssessmentQuestion::TYPE_SINGLE_CHOICE,
            AssessmentQuestion::TYPE_TRUE_FALSE,
            AssessmentQuestion::TYPE_FILL_BLANK,
            AssessmentQuestion::TYPE_CODE_CHOICE,
        ], true)) {
            $option = $question->options->firstWhere('is_correct', true);

            return [
                'selected_option_id' => $option?->id,
                'selected_label' => $option?->label,
            ];
        }

        if ($question->question_type === AssessmentQuestion::TYPE_MULTIPLE_SELECT) {
            $options = $question->options->where('is_correct', true)->values();

            return [
                'selected_option_ids' => $options->pluck('id')->values()->all(),
                'selected_labels' => $options->pluck('label')->values()->all(),
            ];
        }

        if ($question->question_type === AssessmentQuestion::TYPE_ORDERING) {
            $options = $question->options
                ->sortBy(fn ($option) => $option->correct_order ?? $option->order)
                ->values();

            return [
                'ordered_option_ids' => $options->pluck('id')->values()->all(),
                'ordered_labels' => $options->pluck('label')->values()->all(),
            ];
        }

        if ($question->question_type === AssessmentQuestion::TYPE_MATCHING) {
            return [
                'matches' => $question->options
                    ->mapWithKeys(fn ($option) => [(string) $option->id => $option->match_value])
                    ->all(),
                'pairs' => $question->options
                    ->map(fn ($option) => ['left' => $option->label, 'right' => $option->match_value])
                    ->values()
                    ->all(),
            ];
        }

        return [];
    }

    private function normalizeAnswer(mixed $rawAnswer): array
    {
        if (is_array($rawAnswer)) {
            return $rawAnswer;
        }

        if ($rawAnswer === null || $rawAnswer === '') {
            return [];
        }

        return ['selected_option_id' => $rawAnswer];
    }

    private function sortIds(array $ids): array
    {
        $ids = array_map('strval', array_values($ids));
        sort($ids);

        return $ids;
    }

    private function normalizeMatching(array $matches): array
    {
        $normalized = [];

        foreach ($matches as $key => $value) {
            $normalized[(string) $key] = trim(mb_strtolower((string) $value));
        }

        ksort($normalized);

        return $normalized;
    }
}
