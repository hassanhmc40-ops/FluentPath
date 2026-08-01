@extends('layouts.app')

@section('title', 'Admin — New Quiz')

@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold mb-6">New Quiz</h1>

    <form method="POST" action="{{ route('admin.quizzes.store') }}" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="mb-4">
            <label for="lesson_id" class="block text-sm font-medium text-gray-700">Lesson</label>
            <select id="lesson_id" name="lesson_id" class="mt-1 block w-full rounded border-gray-300 shadow-sm px-3 py-2 border">
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}" @selected(old('lesson_id') == $lesson->id)>{{ $lesson->title }}</option>
                @endforeach
            </select>
            @error('lesson_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border" required>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Create quiz</button>
    </form>
</div>
@endsection
