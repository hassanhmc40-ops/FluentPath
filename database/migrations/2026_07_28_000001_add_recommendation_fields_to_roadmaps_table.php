<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmaps', function (Blueprint $table) {
            $table->foreignId('next_lesson_id')->nullable()->constrained('lessons')->nullOnDelete();
            $table->string('next_topic')->nullable();
            $table->text('next_writing_prompt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roadmaps', function (Blueprint $table) {
            $table->dropForeign(['next_lesson_id']);
            $table->dropColumn(['next_lesson_id', 'next_topic', 'next_writing_prompt']);
        });
    }
};
