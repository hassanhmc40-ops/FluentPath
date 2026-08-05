@extends('layouts.app')

@section('title', 'Notifications')

@section('crumb', 'Account')

@section('content')
@php
    $unreadCount = $notifications->where('is_read', false)->count();
@endphp

<div style="max-width: 760px; animation: fadein .4s both;">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 14px;">
        @if ($unreadCount > 0)
            <form method="POST" action="/notifications/mark-all-read">
                @csrf
                <button type="submit" style="border: 1px solid #E0D8CC; background: #FFFDFA; border-radius: 999px; padding: 9px 16px; font: inherit; font-size: 12.5px; cursor: pointer; transition: border-color .2s;" onmouseover="this.style.borderColor='#17211E'" onmouseout="this.style.borderColor='#E0D8CC'">Mark all as read</button>
            </form>
        @endif
    </div>

    @forelse ($notifications as $notification)
        @php
            $isUnread = ! $notification->is_read;
            $bg = $isUnread ? '#FFFDFA' : '#F8F5F0';
            $dot = $isUnread ? '#29C39F' : '#CFC6B8';
            $weight = $isUnread ? '600' : '400';
        @endphp
        @if ($isUnread)
            <form method="POST" action="/notifications/{{ $notification->id }}/read" style="display: flex; gap: 15px; align-items: flex-start; background: {{ $bg }}; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px 20px; margin-bottom: 10px; position: relative; animation: rise .45s {{ 0.06 * $loop->index }}s both;">
                @csrf
                <button type="submit" style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0; background: none; padding: 0; cursor: pointer; border-radius: 16px;" title="Mark as read"></button>
                <span style="width: 8px; height: 8px; border-radius: 50%; margin-top: 7px; flex: none; background: {{ $dot }};"></span>
                <div style="flex: 1; pointer-events: none;">
                    <div style="font-size: 14px; font-weight: {{ $weight }};">{{ $notification->title }}</div>
                    <div style="font-size: 13px; color: #6C6A63; margin-top: 4px; line-height: 1.55;">{{ $notification->message }}</div>
                </div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #A09889; pointer-events: none;">{{ $notification->created_at->diffForHumans() }}</div>
            </form>
        @else
            <div style="display: flex; gap: 15px; align-items: flex-start; background: {{ $bg }}; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px 20px; margin-bottom: 10px; animation: rise .45s {{ 0.06 * $loop->index }}s both;">
                <span style="width: 8px; height: 8px; border-radius: 50%; margin-top: 7px; flex: none; background: {{ $dot }};"></span>
                <div style="flex: 1;">
                    <div style="font-size: 14px; font-weight: {{ $weight }};">{{ $notification->title }}</div>
                    <div style="font-size: 13px; color: #6C6A63; margin-top: 4px; line-height: 1.55;">{{ $notification->message }}</div>
                </div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #A09889;">{{ $notification->created_at->diffForHumans() }}</div>
            </div>
        @endif
    @empty
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 46px; text-align: center; color: #8A8378; font-size: 13.5px; line-height: 1.6;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; color: #55605A; margin-bottom: 8px;">You're all caught up</div>
            Notifications about corrections, recommendations and your roadmap will land here.
        </div>
    @endforelse
</div>
@endsection
