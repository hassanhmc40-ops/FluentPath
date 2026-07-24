<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('placement_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer');
            $table->decimal('score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_answers');
    }
};
