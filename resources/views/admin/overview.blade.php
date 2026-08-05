@extends('layouts.app')

@section('title', 'Admin overview')

@section('crumb', 'Platform usage')

@section('content')
@php
    $pendingJobs = $stats['pending_writing'] + $stats['pending_placement'];
    $maxCompletions = $mostCompletedLessons->max('completions') ?: 1;

    $statCards = [
        ['label' => 'Active students', 'value' => $stats['total_students'], 'note' => 'of '.$stats['total_users'].' accounts', 'color' => '#17211E', 'delay' => '0.05'],
        ['label' => 'Lessons published', 'value' => $stats['total_lessons'], 'note' => 'in the catalog', 'color' => '#0E6B5C', 'delay' => '0.1'],
        ['label' => 'Exercises published', 'value' => $stats['total_quizzes'], 'note' => 'attached to lessons', 'color' => '#17211E', 'delay' => '0.15'],
        ['label' => 'Pending jobs', 'value' => $pendingJobs, 'note' => $pendingJobs > 0
            ? $stats['pending_placement'].' placement · '.$stats['pending_writing'].' writing'
            : 'queue is clear', 'color' => '#E0603B', 'delay' => '0.2'],
    ];

    $jobs = [];
    if ($stats['pending_placement'] > 0) {
        $jobs[] = ['name' => 'EvaluatePlacementTest', 'state' => 'queued', 'time' => 'now', 'dot' => '#CFC6B8'];
    }
    if ($stats['pending_writing'] > 0) {
        $jobs[] = ['name' => 'CorrectWritingSubmission', 'state' => 'queued', 'time' => 'now', 'dot' => '#E0603B'];
    }
@endphp

<div style="display: flex; flex-direction: column; gap: 20px; animation: fadein .4s both;">

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
        @foreach ($statCards as $s)
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 22px; animation: rise .5s {{ $s['delay'] }}s both; transition: transform .25s, box-shadow .25s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 34px rgba(23,33,30,.09)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">{{ $s['label'] }}</div>
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 34px; font-weight: 700; letter-spacing: -1.2px; margin: 8px 0 4px; color: {{ $s['color'] }};">{{ $s['value'] }}</div>
                <div style="font-size: 12px; color: #8A8378;">{{ $s['note'] }}</div>
            </div>
        @endforeach
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .5s .2s both;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700;">Queue monitor</div>
                <span style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">{{ $pendingJobs }} pending</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 18px;">
                @forelse ($jobs as $j)
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 13px;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; flex: none; background: {{ $j['dot'] }}; animation: pulse 2.2s infinite;"></span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #55605A; flex: 1;">{{ $j['name'] }}</span>
                        <span style="color: #8A8378; font-size: 12px;">{{ $j['state'] }}</span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #A09889;">{{ $j['time'] }}</span>
                    </div>
                @empty
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 13px;">
                        <span style="width: 7px; height: 7px; border-radius: 50%; flex: none; background: #29C39F;"></span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #55605A; flex: 1;">Queue healthy</span>
                        <span style="color: #8A8378; font-size: 12px;">idle</span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #A09889;">—</span>
                    </div>
                    <div style="font-size: 12.5px; color: #8A8378; line-height: 1.55; padding: 6px 0 2px;">No AI jobs waiting. New submissions are picked up asynchronously.</div>
                @endforelse
            </div>
        </div>

        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .5s .28s both;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700;">Most completed lessons</div>
                <span style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">Top 10</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 18px;">
                @forelse ($mostCompletedLessons as $t)
                    @php
                        $width = max(4, round($t->completions / $maxCompletions * 100));
                        $level = $t->level ? $t->level->value : '';
                        $skill = $t->skill ? ucfirst($t->skill->value) : '';
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 6px; gap: 10px;">
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $t->title }}</span>
                            <span style="font-family: 'IBM Plex Mono', monospace; color: #8A8378; flex: none;">{{ $t->completions }}</span>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <div style="flex: 1; height: 7px; border-radius: 999px; background: #ECE5DA; overflow: hidden;">
                                <div style="height: 100%; width: {{ $width }}%; background: #0E6B5C; border-radius: 999px; animation: slideL .9s {{ 0.25 + $loop->index * 0.07 }}s both;"></div>
                            </div>
                            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; color: #A09889; flex: none;">{{ $skill }} · {{ $level }}</span>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12.5px; color: #8A8378; line-height: 1.55; padding: 6px 0 2px;">No lessons yet — publish your first lesson from the catalog.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
