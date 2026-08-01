@extends('layouts.app')

@section('title', 'Lessons')

@section('content')
<h1 class="text-2xl font-bold mb-6">Lessons</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse ($lessons as $lesson)
        <div class="bg-white rounded-lg shadow p-6 flex flex-col">
            <div class="flex items-start justify-between mb-3">
                <h2 class="font-semibold text-lg">{{ $lesson->title }}</h2>
                @if (in_array($lesson->id, $completedLessonIds, true))
                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Completed</span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mb-4">
                {{ $lesson->skill->value }} · {{ $lesson->level->value }}
            </p>
            <div class="mt-auto flex gap-3">
                @if (! in_array($lesson->id, $completedLessonIds, true))
                    <form method="POST" action="/lessons/{{ $lesson->id }}/complete">
                        @csrf
                        <button type="submit" class="text-sm bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700">
                            Mark complete
                        </button>
                    </form>
                @endif
                @if ($lesson->quizzes->isNotEmpty())
                    <a href="/quizzes/{{ $lesson->quizzes->first()->id }}" class="text-sm bg-white border border-indigo-600 text-indigo-600 px-3 py-1.5 rounded hover:bg-indigo-50">
                        Take quiz
                    </a>
                @endif
            </div>
        </div>
    @empty
        <p class="text-gray-500">No lessons available yet.</p>
    @endforelse
</div>
@endsection
