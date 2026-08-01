@extends('layouts.app')

@section('title', 'Admin — Lessons')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Lessons</h1>
    <a href="{{ route('admin.lessons.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">New lesson</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-3 px-4">Title</th>
                <th class="py-3 px-4">Skill</th>
                <th class="py-3 px-4">Level</th>
                <th class="py-3 px-4">Quizzes</th>
                <th class="py-3 px-4"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lessons as $lesson)
                <tr class="border-b">
                    <td class="py-3 px-4">{{ $lesson->title }}</td>
                    <td class="py-3 px-4">{{ $lesson->skill->value }}</td>
                    <td class="py-3 px-4">{{ $lesson->level->value }}</td>
                    <td class="py-3 px-4">{{ $lesson->quizzes_count }}</td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-indigo-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" class="inline" onsubmit="return confirm('Delete this lesson?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-3">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 px-4 text-gray-500">No lessons yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
