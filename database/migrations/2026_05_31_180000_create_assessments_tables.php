<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('assessment_type', 30)->default('lesson');
            $table->string('question_type', 40);
            $table->text('question');
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('points')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'assessment_type', 'lesson_id', 'is_active'], 'aq_course_type_lesson_active_idx');
            $table->index(['lesson_id', 'order'], 'aq_lesson_order_idx');
        });

        Schema::create('assessment_question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->text('label');
            $table->text('value')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('correct_order')->nullable();
            $table->text('match_value')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['assessment_question_id', 'order'], 'aqo_question_order_idx');
        });

        Schema::create('assessment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('assessment_type', 30)->default('lesson');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('max_score')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'course_id', 'assessment_type'], 'aa_user_course_type_idx');
            $table->index(['lesson_id', 'assessment_type'], 'aa_lesson_type_idx');
        });

        Schema::create('assessment_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('assessment_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_question_id')->constrained()->cascadeOnDelete();
            $table->json('answer')->nullable();
            $table->json('correct_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('score')->default(0);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['assessment_attempt_id', 'assessment_question_id'], 'assessment_answer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('assessment_question_options');
        Schema::dropIfExists('assessment_questions');
    }
};
