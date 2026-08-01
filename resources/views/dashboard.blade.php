@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-6">Dashboard</h1>

@if ($data === null)
    <div class="bg-white p-8 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Admin quick links</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('admin.lessons.index') }}" class="border rounded p-4 hover:border-indigo-400">
                <span class="block font-medium">Lessons</span>
                <span class="text-sm text-gray-500">Manage the lesson catalog</span>
            </a>
            <a href="{{ route('admin.quizzes.index') }}" class="border rounded p-4 hover:border-indigo-400">
                <span class="block font-medium">Quizzes</span>
                <span class="text-sm text-gray-500">Manage quizzes</span>
            </a>
            <a href="{{ route('admin.quiz-questions.index') }}" class="border rounded p-4 hover:border-indigo-400">
                <span class="block font-medium">Quiz Questions</span>
                <span class="text-sm text-gray-500">Manage quiz questions</span>
            </a>
            <a href="{{ route('admin.placement-questions.index') }}" class="border rounded p-4 hover:border-indigo-400">
                <span class="block font-medium">Placement Questions</span>
                <span class="text-sm text-gray-500">Manage the placement test bank</span>
            </a>
        </div>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">CEFR Level</p>
            <p class="text-2xl font-bold">{{ $data['cefr_level']?->value ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Current Week</p>
            <p class="text-2xl font-bold">{{ $data['current_week'] ?? '—' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Learning Streak</p>
            <p class="text-2xl font-bold">{{ $data['learning_streak'] }} days</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <p class="text-sm text-gray-500">Roadmap Progress</p>
            <p class="text-2xl font-bold">{{ $data['overall_progress_percentage'] }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold mb-3">Lessons Completed</h2>
            <p class="text-2xl font-bold">{{ $data['lessons']['completed'] }} <span class="text-base font-normal text-gray-500">/ {{ $data['lessons']['total'] }}</span></p>
            <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full"
                     style="width: {{ $data['lessons']['total'] > 0 ? ($data['lessons']['completed'] / $data['lessons']['total'] * 100) : 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold mb-3">Next Recommended Action</h2>
            @if ($data['next_recommended_action'])
                <p class="font-medium">{{ $data['next_recommended_action']['lesson_title'] ?? 'Writing practice' }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $data['next_recommended_action']['topic'] ?? $data['next_recommended_action']['writing_prompt'] }}</p>
                @if ($data['next_recommended_action']['lesson_id'])
                    <a href="/lessons" class="inline-block mt-3 text-sm text-indigo-600 hover:underline">Go to lessons →</a>
                @else
                    <a href="/writing" class="inline-block mt-3 text-sm text-indigo-600 hover:underline">Write now →</a>
                @endif
            @else
                <p class="text-sm text-gray-600">No roadmap yet.</p>
                <a href="/placement-test" class="inline-block mt-3 text-sm text-indigo-600 hover:underline">Take the placement test →</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold mb-3">Skill Trends</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Grammar</dt>
                    <dd>
                        @if ($data['grammar_improvement']['start_score'] !== null)
                            {{ $data['grammar_improvement']['start_score'] }} → {{ $data['grammar_improvement']['current_score'] }}
                            <span class="ml-2 px-2 py-0.5 rounded text-xs {{ $data['grammar_improvement']['trend'] === 'improving' ? 'bg-green-100 text-green-700' : ($data['grammar_improvement']['trend'] === 'declining' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($data['grammar_improvement']['trend']) }}
                            </span>
                        @else
                            Not enough data yet
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Vocabulary</dt>
                    <dd>
                        @if ($data['vocabulary_improvement']['start_score'] !== null)
                            {{ $data['vocabulary_improvement']['start_score'] }} → {{ $data['vocabulary_improvement']['current_score'] }}
                            <span class="ml-2 px-2 py-0.5 rounded text-xs {{ $data['vocabulary_improvement']['trend'] === 'improving' ? 'bg-green-100 text-green-700' : ($data['vocabulary_improvement']['trend'] === 'declining' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ ucfirst($data['vocabulary_improvement']['trend']) }}
                            </span>
                        @else
                            Not enough data yet
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-5">
            <h2 class="font-semibold mb-3">Writing Score History</h2>
            @if (count($data['writing_score_history']) > 0)
                <ul class="space-y-2 text-sm">
                    @foreach ($data['writing_score_history'] as $entry)
                        <li class="flex justify-between border-b pb-2">
                            <span class="text-gray-600">{{ \Illuminate\Support\Carbon::parse($entry['submitted_at'])->format('M j, Y') }}</span>
                            <span class="font-medium">{{ $entry['score'] }}%</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-600">No corrected writing submissions yet.</p>
                <a href="/writing" class="inline-block mt-3 text-sm text-indigo-600 hover:underline">Submit your first piece →</a>
            @endif
        </div>
    </div>
@endif
@endsection
