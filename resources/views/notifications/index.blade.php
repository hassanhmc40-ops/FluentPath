@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<h1 class="text-2xl font-bold mb-6">Notifications</h1>

<div class="space-y-4">
    @forelse ($notifications as $notification)
        <div class="bg-white rounded-lg shadow p-6 flex items-start justify-between gap-4 {{ $notification->is_read ? 'opacity-70' : 'border-l-4 border-indigo-500' }}">
            <div>
                <p class="font-semibold">{{ $notification->title }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $notification->created_at->format('M j, Y H:i') }}</p>
            </div>
            @if (! $notification->is_read)
                <form method="POST" action="/notifications/{{ $notification->id }}/read">
                    @csrf
                    <button type="submit" class="text-sm text-indigo-600 hover:underline whitespace-nowrap">Mark as read</button>
                </form>
            @endif
        </div>
    @empty
        <p class="text-gray-500">You're all caught up — no notifications.</p>
    @endforelse
</div>
@endsection
