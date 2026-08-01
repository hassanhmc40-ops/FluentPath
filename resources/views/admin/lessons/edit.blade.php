@extends('layouts.app')

@section('title', 'Admin — Edit Lesson')

@section('content')
<div class="max-w-lg">
    <h1 class="text-2xl font-bold mb-6">Edit Lesson</h1>

    <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title', $lesson->title) }}"
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border" required>
            @error('title')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="skill" class="block text-sm font-medium text-gray-700">Skill</label>
            <select id="skill" name="skill" class="mt-1 block w-full rounded border-gray-300 shadow-sm px-3 py-2 border">
                @foreach ($skills as $skill)
                    <option value="{{ $skill->value }}" @selected(old('skill', $lesson->skill->value) === $skill->value)>{{ $skill->value }}</option>
                @endforeach
            </select>
            @error('skill')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="level" class="block text-sm font-medium text-gray-700">CEFR Level</label>
            <select id="level" name="level" class="mt-1 block w-full rounded border-gray-300 shadow-sm px-3 py-2 border">
                @foreach ($levels as $level)
                    <option value="{{ $level->value }}" @selected(old('level', $lesson->level->value) === $level->value)>{{ $level->value }}</option>
                @endforeach
            </select>
            @error('level')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">Update lesson</button>
    </form>
</div>
@endsection
