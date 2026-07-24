<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at')->useCurrent();
            $table->string('status')->default('pending');
            $table->string('cefr_level')->nullable();
            $table->decimal('grammar_score', 5, 2)->nullable();
            $table->decimal('vocabulary_score', 5, 2)->nullable();
            $table->decimal('writing_score', 5, 2)->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->text('reasoning')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_tests');
    }
};
