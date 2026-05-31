# Assessments Feature

This update adds automatically graded lesson exercises and final course exams to Klaarsite Academy.

## Included question types

1. Single choice
2. True / false
3. Multiple select
4. Ordering steps
5. Matching
6. Fill blank from choices
7. Code choice

No written/free-code answers are included in this release, so no manual review or AI grading is required.

## Admin usage

Open a course in the admin builder, then click **Exercises & Exam**.

URL pattern:

```txt
/admin/courses/{course}/assessments
```

From that page you can create:

- Lesson exercise questions linked to a specific lesson.
- Final exam questions linked to the course.

Each question has:

- Question type
- Question text
- Points
- Optional explanation for the correct answer
- Active/hidden status
- Options/items according to the type

## Student usage

If a lesson has questions, the lesson page shows a lesson exercise button:

```txt
/dashboard/learn/{course:slug}/lessons/{lesson}/exercise
```

If a course has final exam questions, the course overview page shows a final exam card:

```txt
/dashboard/learn/{course:slug}/final-exam
```

After submission, the student sees:

- Score
- Max score
- Percentage
- Passed / try again status
- Per-question correctness
- Correct answers only after clicking “Show correct answers”

## Deployment

Run:

```bash
php artisan migrate --force
npm install
npm run build
php artisan optimize:clear
php artisan optimize
```

## New database tables

- assessment_questions
- assessment_question_options
- assessment_attempts
- assessment_answers
