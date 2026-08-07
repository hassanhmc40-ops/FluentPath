<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\PlacementQuestion;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the content catalog (placement questions, lessons, quizzes and quiz
 * questions) from the data packs in database/data/.
 *
 * Idempotent: the catalog tables are truncated and re-inserted on every run.
 * The lesson key → id mapping is exposed on $lessonIds so DatabaseSeeder can
 * build roadmaps from the roadmap templates.
 */
class ContentSeeder extends Seeder
{
    /** @var array<string, int> lesson key → lesson id */
    public array $lessonIds = [];

    public function run(): void
    {
        $grammarReading = require database_path('data/grammar_reading.php');
        $vocabularyWriting = require database_path('data/vocabulary_writing.php');

        $placementQuestions = array_merge(
            $grammarReading['placement_questions'],
            $vocabularyWriting['placement_questions'],
        );

        $lessons = array_merge($grammarReading['lessons'], $vocabularyWriting['lessons']);

        $quizzes = array_merge($grammarReading['quizzes'], $vocabularyWriting['quizzes']);

        Schema::disableForeignKeyConstraints();

        PlacementQuestion::query()->delete();
        Lesson::query()->delete();
        Quiz::query()->delete();
        QuizQuestion::query()->delete();

        Schema::enableForeignKeyConstraints();

        foreach ($this->orderedPlacementQuestions($placementQuestions) as $question) {
            PlacementQuestion::create($question);
        }

        foreach ($lessons as $key => $lesson) {
            $this->lessonIds[$key] = Lesson::create($lesson)->id;
        }

        foreach ($quizzes as $key => $quiz) {
            if (! isset($this->lessonIds[$key])) {
                continue;
            }

            $created = Quiz::create([
                'lesson_id' => $this->lessonIds[$key],
                'title' => $quiz['title'],
                'description' => $quiz['description'] ?? null,
            ]);

            foreach ($quiz['questions'] as $question) {
                QuizQuestion::create([
                    'quiz_id' => $created->id,
                    'question' => $question['question'],
                    'option_a' => $question['options']['a'],
                    'option_b' => $question['options']['b'],
                    'option_c' => $question['options']['c'],
                    'option_d' => $question['options']['d'],
                    'correct_answer' => $question['correct'],
                ]);
            }
        }
    }

    /**
     * Order placement questions by skill (grammar, vocabulary, reading,
     * writing) and by CEFR level (A1 → C1) so the placement test presents
     * them in a coherent progression.
     *
     * @param  list<array{skill: string, level: string, question: string}>  $questions
     * @return list<array{skill: string, level: string, question: string}>
     */
    protected function orderedPlacementQuestions(array $questions): array
    {
        $skillOrder = ['grammar' => 0, 'vocabulary' => 1, 'reading' => 2, 'writing' => 3];
        $levelOrder = ['A1' => 0, 'A2' => 1, 'B1' => 2, 'B2' => 3, 'C1' => 4];

        usort($questions, function (array $a, array $b) use ($skillOrder, $levelOrder) {
            return [$skillOrder[$a['skill']] ?? 99, $levelOrder[$a['level']] ?? 99]
                <=> [$skillOrder[$b['skill']] ?? 99, $levelOrder[$b['level']] ?? 99];
        });

        return $questions;
    }
}
