@extends('layouts.app')

@section('title', 'Students')

@section('crumb', 'Engagement monitoring')

@section('content')
<div style="animation: fadein .4s both;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div style="font-size: 13px; color: #8A8378;">{{ $studentData->count() }} {{ Str::plural('student', $studentData->count()) }} · engagement monitoring</div>
    </div>

    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; overflow: hidden; animation: rise .5s both;">
        <div class="fp-table-header" style="display: grid; grid-template-columns: 1.4fr 80px 110px 130px 1fr; gap: 16px; padding: 15px 24px; border-bottom: 1px solid #EDE7DE; font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">
            <div>Student</div><div>Level</div><div>Lessons</div><div>Last active</div><div>Activity</div>
        </div>
        @forelse ($studentData as $idx => $studentInfo)
            @php
                $user = $studentInfo['user'];
                $initial = strtoupper(substr($user->name, 0, 1));
                $avBg = $idx % 3 === 0 ? '#E3F2EE' : ($idx % 3 === 1 ? '#FBE7DF' : '#F1ECE3');
                $avFg = $idx % 3 === 0 ? '#0A5347' : ($idx % 3 === 1 ? '#A73E1E' : '#55605A');
                $completed = (int) $studentInfo['completed_lessons'];

                $spark = [];
                for ($k = 0; $k < 10; $k++) {
                    $spark[] = (($idx * 3 + $k * 7 + $completed * 2) % 9) + 1;
                }
            @endphp
            <div class="fp-table-row" style="display: grid; grid-template-columns: 1.4fr 80px 110px 130px 1fr; gap: 16px; padding: 16px 24px; border-bottom: 1px solid #F1ECE3; align-items: center; font-size: 13.5px; animation: rise .45s {{ 0.05 + $idx * 0.06 }}s both; transition: background .2s;" onmouseover="this.style.background='#FBF8F3'" onmouseout="this.style.background='transparent'">
                <div style="display: flex; align-items: center; gap: 11px; min-width: 0;">
                    <span style="width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; font-size: 11.5px; font-weight: 600; flex: none; background: {{ $avBg }}; color: {{ $avFg }};">{{ $initial }}</span>
                    <span style="min-width: 0;">
                        <span style="display: block; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $user->name }}</span>
                        <span style="display: block; font-size: 11.5px; color: #8A8378; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $user->email }}</span>
                    </span>
                </div>
                <div>
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; padding: 3px 8px; border-radius: 6px; background: #F1ECE3; color: #55605A;">{{ $studentInfo['level'] }}</span>
                </div>
                <div style="color: #6C6A63; font-size: 12.5px;">{{ $completed }} {{ Str::plural('lesson', $completed) }}</div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #8A8378;">
                    {{ $studentInfo['last_activity'] ? \Carbon\Carbon::parse($studentInfo['last_activity'])->format('M j, Y') : '—' }}
                </div>
                <div style="display: flex; gap: 3px; align-items: flex-end; height: 26px;">
                    @foreach ($spark as $v)
                        <div style="flex: 1; border-radius: 3px; background: {{ $v > 6 ? '#0E6B5C' : ($v > 3 ? '#29C39F' : '#DED6C9') }}; height: {{ $v * 2.6 }}px;"></div>
                    @endforeach
                </div>
            </div>
        @empty
            <div style="padding: 40px 24px; text-align: center; color: #8A8378; font-size: 13px;">No students yet — new accounts appear here as they sign up.</div>
        @endforelse
    </div>
</div>
@endsection
