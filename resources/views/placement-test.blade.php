@extends('layouts.app')

@section('title', 'Placement Test')

@section('head')
    @if ($processing)
        <meta http-equiv="refresh" content="5">
    @endif
@endsection

@section('content')
<h1 class="text-2xl font-bold mb-6">Placement Test</h1>

@if ($processing)
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="mx-auto w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
        <p class="font-medium">Your placement test is being evaluated by the AI mentor...</p>
        <p class="text-sm text-gray-500 mt-2">This page refreshes automatically. You'll see your CEFR level and scores here when it's done.</p>
    </div>
@elseif ($test)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold mb-4">Your Results</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-gray-600">CEFR Level</dt>
                    <dd class="font-bold text-indigo-600">{{ $test->cefr_level?->value ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Grammar</dt>
                    <dd>{{ $test->grammar_score ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Vocabulary</dt>
                    <dd>{{ $test->vocabulary_score ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Writing</dt>
                    <dd>{{ $test->writing_score ?? '—' }}</dd>
                </div>
            </dl>

            @if ($test->strengths)
                <h3 class="font-semibold mt-6 mb-2">Strengths</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                    @foreach ($test->strengths as $strength)
                        <li>{{ $strength }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($test->weaknesses)
                <h3 class="font-semibold mt-6 mb-2">Areas to Improve</h3>
                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                    @foreach ($test->weaknesses as $weakness)
                        <li>{{ $weakness }}</li>
                    @endforeach
                </ul>
            @endif

            <a href="/roadmap" class="inline-block mt-6 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">View my roadmap</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold mb-4">Your Answers</h2>
            <ul class="space-y-3 text-sm">
                @foreach ($test->placementAnswers as $answer)
                    <li>
                        <p class="font-medium text-gray-800">{{ $answer->placementQuestion->question }}</p>
                        <p class="text-gray-600 mt-0.5">{{ $answer->answer }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <p class="text-gray-600">
            Answer the questions below as best you can. The AI mentor will evaluate your responses
            and assign you a CEFR level (A1–C1), then generate a personalized roadmap.
        </p>
    </div>

    <form method="POST" action="/placement-test" class="space-y-6">
        @csrf

        @foreach ($questions as $question)
            <div class="bg-white rounded-lg shadow p-6">
                <label for="answer-{{ $question->id }}" class="font-medium block mb-2">
                    {{ $loop->iteration }}. {{ $question->question }}
                    <span class="ml-2 text-xs text-gray-400">{{ $question->skill->value }} · {{ $question->level->value }}</span>
                </label>
                <input type="hidden" name="answers[{{ $loop->index }}][placement_question_id]" value="{{ $question->id }}">
                <textarea id="answer-{{ $question->id }}" name="answers[{{ $loop->index }}][answer]" rows="3"
                          class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                          placeholder="Write your answer in English..." required>{{ old("answers.{$loop->index}.answer") }}</textarea>
            </div>
        @endforeach

        @error('answers')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
            Submit placement test
        </button>
    </form>
@endif
@endsection
