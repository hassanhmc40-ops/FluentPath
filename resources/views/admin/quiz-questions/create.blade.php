@extends('layouts.app')

@section('title', 'Admin — New Quiz Question')

@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold mb-6">New Quiz Question</h1>

    <form method="POST" action="{{ route('admin.quiz-questions.store') }}" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="mb-4">
            <label for="quiz_id" class="block text-sm font-medium text-gray-700">Quiz</label>
            <select id="quiz_id" name="quiz_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm px-3 py-2 border">
                @foreach ($quizzes as $quiz)
                    <option value="{{ $quiz->id }}" @selected(old('quiz_id') == $quiz->id)>{{ $quiz->title }} — {{ $quiz->lesson->title }}</option>
                @endforeach
            </select>
            @error('quiz_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="question" class="block text-sm font-medium text-gray-700">Question</label>
            <textarea id="question" name="question" rows="2"
                      class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border" required>{{ old('question') }}</textarea>
            @error('question')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            @foreach (['a', 'b', 'c', 'd'] as $option)
                <div>
                    <label for="option_{{ $option }}" class="block text-sm font-medium text-gray-700">Option {{ strtoupper($option) }}</label>
                    <input type="text" id="option_{{ $option }}" name="option_{{ $option }}" value="{{ old('option_' . $option) }}"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border" required>
                </div>
            @endforeach
        </div>

        <div class="mb-6">
            <label for="correct_answer" class="block text-sm font-medium text-gray-700">Correct Answer</label>
            <select id="correct_answer" name="correct_answer" class="mt-1 block w-full rounded border-gray-300 shadow-sm px-3 py-2 border">
                @foreach (['a', 'b', 'c', 'd'] as $option)
                    <option value="{{ $option }}" @selected(old('correct_answer') === $option)>{{ strtoupper($option) }}</option>
                @endforeach
            </select>
            @error('correct_answer')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create question</button>
    </form>
</div>
@endsection
