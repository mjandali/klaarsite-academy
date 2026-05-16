<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('course_format', ['text', 'video', 'mixed'])
                ->default('mixed')
                ->after('meta_description');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('video_provider', ['youtube', 'vimeo'])
                ->nullable()
                ->after('video_url');
            $table->string('video_id', 32)
                ->nullable()
                ->after('video_provider');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['video_provider', 'video_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('course_format');
        });
    }
};
