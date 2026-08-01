@extends('layouts.app')

@section('title', 'Admin — Quizzes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Quizzes</h1>
    <a href="{{ route('admin.quizzes.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">New quiz</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-3 px-4">Title</th>
                <th class="py-3 px-4">Lesson</th>
                <th class="py-3 px-4">Questions</th>
                <th class="py-3 px-4"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($quizzes as $quiz)
                <tr class="border-b">
                    <td class="py-3 px-4">{{ $quiz->title }}</td>
                    <td class="py-3 px-4">{{ $quiz->lesson->title }}</td>
                    <td class="py-3 px-4">{{ $quiz->quiz_questions_count }}</td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-indigo-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" class="inline" onsubmit="return confirm('Delete this quiz?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-3">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-6 px-4 text-gray-500">No quizzes yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
