<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->text('original_text');
            $table->text('corrected_text')->nullable();
            $table->text('grammar_feedback')->nullable();
            $table->text('vocabulary_feedback')->nullable();
            $table->text('fluency_feedback')->nullable();
            $table->json('mistakes')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('next_topics')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_submissions');
    }
};
