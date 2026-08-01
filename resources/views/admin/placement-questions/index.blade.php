@extends('layouts.app')

@section('title', 'Admin — Placement Questions')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Placement Questions</h1>
    <a href="{{ route('admin.placement-questions.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">New question</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-3 px-4">Question</th>
                <th class="py-3 px-4">Skill</th>
                <th class="py-3 px-4">Level</th>
                <th class="py-3 px-4"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($placementQuestions as $question)
                <tr class="border-b">
                    <td class="py-3 px-4">{{ $question->question }}</td>
                    <td class="py-3 px-4">{{ $question->skill->value }}</td>
                    <td class="py-3 px-4">{{ $question->level->value }}</td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">
                        <a href="{{ route('admin.placement-questions.edit', $question) }}" class="text-indigo-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.placement-questions.destroy', $question) }}" class="inline" onsubmit="return confirm('Delete this question?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline ml-3">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-6 px-4 text-gray-500">No placement questions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
