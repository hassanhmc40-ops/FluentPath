@extends('layouts.app')

@section('title', $quiz->title)

@section('content')
<h1 class="text-2xl font-bold mb-2">{{ $quiz->title }}</h1>
<p class="text-gray-600 mb-6">{{ $quiz->description }}</p>

@if ($lastScore !== null)
    <div class="bg-white rounded-lg shadow p-6 mb-6 border-l-4 {{ $lastScore >= 70 ? 'border-green-500' : 'border-red-500' }}">
        <p class="font-semibold {{ $lastScore >= 70 ? 'text-green-700' : 'text-red-700' }}">
            Last attempt: {{ $lastScore }}%
        </p>
    </div>
@endif

<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="POST" action="/quizzes/{{ $quiz->id }}/attempt">
        @csrf

        @foreach ($quiz->quizQuestions as $question)
            <fieldset class="mb-8">
                <legend class="font-medium mb-3">
                    {{ $loop->iteration }}. {{ $question->question }}
                </legend>
                @foreach (['a', 'b', 'c', 'd'] as $option)
                    <label class="flex items-center gap-2 py-1 text-sm">
                        <input type="radio" name="answers[{{ $question->id }}][selected_option]" value="{{ $option }}" required>
                        {{ $question->{'option_' . $option} }}
                    </label>
                @endforeach
                <input type="hidden" name="answers[{{ $question->id }}][quiz_question_id]" value="{{ $question->id }}">
            </fieldset>
        @endforeach

        @error('answers')
            <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
            Submit quiz
        </button>
    </form>
</div>

@if ($attempts->isNotEmpty())
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold mb-4">Attempt History</h2>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b">
                    <th class="py-2">Date</th>
                    <th class="py-2">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attempts as $attempt)
                    <tr class="border-b">
                        <td class="py-2">{{ $attempt->completed_at->format('M j, Y H:i') }}</td>
                        <td class="py-2 font-medium">{{ $attempt->score }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
