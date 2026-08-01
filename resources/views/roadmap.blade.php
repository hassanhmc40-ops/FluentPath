@extends('layouts.app')

@section('title', 'My Roadmap')

@section('head')
    @if ($processing)
        <meta http-equiv="refresh" content="5">
    @endif
@endsection

@section('content')
<h1 class="text-2xl font-bold mb-6">My Roadmap</h1>

@if ($processing)
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="mx-auto w-10 h-10 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
        <p class="font-medium">Your personalized roadmap is being generated...</p>
        <p class="text-sm text-gray-500 mt-2">This page refreshes automatically.</p>
    </div>
@elseif (! $roadmap)
    <div class="bg-white rounded-lg shadow p-8 text-center">
        @if ($roadmap && $roadmap->status === \App\Enums\RoadmapStatus::Failed)
            <p class="font-medium text-red-600 mb-2">The last generation attempt failed. Please try again.</p>
        @else
            <p class="font-medium mb-2">You don't have a roadmap yet.</p>
            <p class="text-sm text-gray-500 mb-6">Take the placement test first, then generate your personalized learning path.</p>
        @endif

        @php
            $hasAnalyzedTest = \App\Models\PlacementTest::latestAnalyzedFor(auth()->id())->exists();
        @endphp

        @if ($hasAnalyzedTest)
            <form method="POST" action="/roadmap">
                @csrf
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                    Generate my roadmap
                </button>
            </form>
        @else
            <a href="/placement-test" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                Take the placement test
            </a>
        @endif
    </div>
@else
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold">{{ $roadmap->title }}</h2>
        @if ($roadmap->summary)
            <p class="text-sm text-gray-600 mt-2">{{ $roadmap->summary }}</p>
        @endif
        <form method="POST" action="/roadmap" class="mt-4">
            @csrf
            <button type="submit" class="text-sm text-indigo-600 hover:underline">Regenerate roadmap</button>
        </form>
    </div>

    @foreach ($roadmap->roadmapWeeks->sortBy('week_number') as $week)
        <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
            <div class="px-6 py-4 bg-indigo-50 border-b">
                <h3 class="font-semibold">Week {{ $week->week_number }}</h3>
                <p class="text-sm text-gray-600">{{ $week->objective }}</p>
            </div>
            <ul class="divide-y">
                @foreach ($week->roadmapWeekLessons->sortBy('display_order') as $weekLesson)
                    <li class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if (in_array($weekLesson->lesson_id, $completedLessonIds, true))
                                <span class="text-green-600">✓</span>
                            @else
                                <span class="text-gray-300">○</span>
                            @endif
                            <div>
                                <p class="font-medium">{{ $weekLesson->lesson->title }}</p>
                                <p class="text-xs text-gray-500">{{ $weekLesson->lesson->skill->value }} · {{ $weekLesson->lesson->level->value }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if (! in_array($weekLesson->lesson_id, $completedLessonIds, true))
                                <form method="POST" action="/lessons/{{ $weekLesson->lesson->id }}/complete">
                                    @csrf
                                    <button type="submit" class="text-sm text-indigo-600 hover:underline">Mark complete</button>
                                </form>
                            @endif
                            @if ($weekLesson->lesson->quizzes->isNotEmpty())
                                <a href="/quizzes/{{ $weekLesson->lesson->quizzes->first()->id }}" class="text-sm bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">Take quiz</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endif
@endsection
