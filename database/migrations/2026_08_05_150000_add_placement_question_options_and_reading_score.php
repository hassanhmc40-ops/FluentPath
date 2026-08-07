<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('placement_questions', function (Blueprint $table) {
            $table->string('option_a')->nullable()->after('question');
            $table->string('option_b')->nullable()->after('option_a');
            $table->string('option_c')->nullable()->after('option_b');
            $table->string('option_d')->nullable()->after('option_c');
            $table->string('correct_answer')->nullable()->after('option_d');
        });

        Schema::table('placement_tests', function (Blueprint $table) {
            $table->decimal('reading_score', 5, 2)->nullable()->after('vocabulary_score');
        });
    }

    public function down(): void
    {
        Schema::table('placement_questions', function (Blueprint $table) {
            $table->dropColumn(['option_a', 'option_b', 'option_c', 'option_d', 'correct_answer']);
        });

        Schema::table('placement_tests', function (Blueprint $table) {
            $table->dropColumn('reading_score');
        });
    }
};
