@extends('layouts.app')

@section('title', 'Writing Practice')

@section('content')
<h1 class="text-2xl font-bold mb-6">Writing Practice</h1>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold mb-4">Submit a Piece</h2>

        <form method="POST" action="/writing">
            @csrf

            <div class="mb-4">
                <label for="prompt" class="block text-sm font-medium text-gray-700">Prompt</label>
                <textarea id="prompt" name="prompt" rows="2"
                          class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                          placeholder="e.g. Describe your favorite place in the world.">{{ old('prompt') }}</textarea>
                @error('prompt')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="original_text" class="block text-sm font-medium text-gray-700">Your Writing</label>
                <textarea id="original_text" name="original_text" rows="8"
                          class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 border"
                          placeholder="Write your text in English..." required>{{ old('original_text') }}</textarea>
                @error('original_text')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                Submit for correction
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($submissions as $submission)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs text-gray-500">{{ $submission->submitted_at->format('M j, Y H:i') }}</p>
                    @if ($submission->status === \App\Enums\WritingSubmissionStatus::Corrected)
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Score: {{ $submission->score }}%</span>
                    @else
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">{{ ucfirst($submission->status->value) }}...</span>
                    @endif
                </div>
                <p class="font-medium text-sm">{{ $submission->prompt }}</p>
                <p class="text-sm text-gray-600 mt-2 whitespace-pre-wrap">{{ $submission->original_text }}</p>

                @if ($submission->status === \App\Enums\WritingSubmissionStatus::Corrected && $submission->corrected_text)
                    <details class="mt-4">
                        <summary class="text-sm text-indigo-600 cursor-pointer">Show correction</summary>
                        <div class="mt-3 bg-green-50 border border-green-200 rounded p-4">
                            <p class="text-sm whitespace-pre-wrap">{{ $submission->corrected_text }}</p>
                            @if ($submission->grammar_feedback)
                                <p class="text-sm text-gray-700 mt-3"><strong>Grammar:</strong> {{ $submission->grammar_feedback }}</p>
                            @endif
                            @if ($submission->vocabulary_feedback)
                                <p class="text-sm text-gray-700 mt-1"><strong>Vocabulary:</strong> {{ $submission->vocabulary_feedback }}</p>
                            @endif
                            @if ($submission->fluency_feedback)
                                <p class="text-sm text-gray-700 mt-1"><strong>Fluency:</strong> {{ $submission->fluency_feedback }}</p>
                            @endif
                        </div>
                    </details>
                @endif
            </div>
        @empty
            <p class="text-gray-500">No submissions yet.</p>
        @endforelse
    </div>
</div>
@endsection
