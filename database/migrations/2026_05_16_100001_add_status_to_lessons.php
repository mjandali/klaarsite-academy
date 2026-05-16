<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->enum('status', ['draft', 'published'])->default('draft')->after('is_published');
            $table->dropColumn('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('order');
            $table->dropColumn('status');
        });
    }
};
