@extends('layouts.app')

@section('title', 'Progress')

@section('crumb', 'Account')

@section('content')
@php
    $dotColors = [
        'lesson' => '#0E6B5C',
        'quiz' => '#29C39F',
        'writing' => '#E0603B',
    ];
@endphp

<div style="max-width: 900px; animation: fadein .4s both;">
    @forelse ($events as $event)
        @php
            $dot = $dotColors[$event['type']] ?? '#CFC6B8';
        @endphp
        <div style="display: grid; grid-template-columns: 92px 24px 1fr; animation: rise .5s {{ 0.06 * $loop->index }}s both;">
            <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #8A8378; padding: 18px 16px 18px 0; text-align: right;">{{ $event['date']->format('M j') }}</div>
            <div style="display: flex; flex-direction: column; align-items: center;">
                <div style="width: 11px; height: 11px; border-radius: 50%; margin-top: 21px; background: {{ $dot }};"></div>
                @if (! $loop->last)
                    <div style="flex: 1; width: 1.5px; background: #E0D8CC;"></div>
                @endif
            </div>
            <div style="padding: 14px 0 22px 18px;">
                <div style="font-size: 14.5px; font-weight: 500;">{{ $event['title'] }}</div>
                <div style="font-size: 13px; color: #6C6A63; margin-top: 4px; line-height: 1.55;">{{ $event['description'] }}</div>
            </div>
        </div>
    @empty
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 46px; text-align: center; color: #8A8378; font-size: 13.5px; line-height: 1.6;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; color: #55605A; margin-bottom: 8px;">No activity yet</div>
            Complete a lesson, take an exercise, or write something — your timeline will build itself here.
        </div>
    @endforelse
</div>
@endsection
