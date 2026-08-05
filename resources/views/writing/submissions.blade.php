@extends('layouts.app')

@section('title', 'My submissions')

@section('crumb', 'Practice')

@section('content')
@php
    use App\Enums\WritingSubmissionStatus;
@endphp

<div style="animation: fadein .4s both;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <span style="font-size: 13px; color: #8A8378;">{{ $submissions->count() }} {{ Str::plural('submission', $submissions->count()) }}</span>
        <a href="/writing" style="font-size: 12.5px; color: #0E6B5C; text-decoration: none;" onmouseover="this.style.color='#E0603B'" onmouseout="this.style.color='#0E6B5C'">← Back to studio</a>
    </div>

    @forelse ($submissions as $submission)
        @php
            $state = match ($submission->status) {
                WritingSubmissionStatus::Corrected => 'ok',
                WritingSubmissionStatus::Failed => 'failed',
                default => 'pending',
            };
            $chipBg = $state === 'ok' ? '#E3F2EE' : ($state === 'pending' ? '#F1ECE3' : '#FBE7DF');
            $chipFg = $state === 'ok' ? '#0A5347' : ($state === 'pending' ? '#6C6A63' : '#A73E1E');
            $scoreFg = $state === 'ok' ? '#0E6B5C' : '#B5AC9D';
            $action = $state === 'ok' ? 'View' : ($state === 'failed' ? 'Retry' : 'Waiting');
        @endphp
        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; overflow: hidden; margin-bottom: 14px; animation: rise .45s {{ 0.06 * $loop->index }}s both;">
            <div style="display: grid; grid-template-columns: 1.6fr 130px 90px 130px 110px; gap: 16px; padding: 15px 24px; border-bottom: 1px solid #EDE7DE; font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">
                <div>Task</div><div>Submitted</div><div>Score</div><div>Status</div><div style="text-align: right;"></div>
            </div>
            <div style="display: grid; grid-template-columns: 1.6fr 130px 90px 130px 110px; gap: 16px; padding: 17px 24px; align-items: center; font-size: 13.5px; transition: background .2s;" onmouseover="this.style.background='#FBF8F3'" onmouseout="this.style.background='transparent'">
                <div style="font-weight: 500;">{{ $submission->prompt }}</div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #8A8378;">{{ $submission->submitted_at->format('M j') }}</div>
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; font-weight: 700; color: {{ $scoreFg }};">{{ $state === 'ok' ? (int) round((float) $submission->score) : '—' }}</div>
                <div><span style="font-size: 11.5px; padding: 4px 10px; border-radius: 999px; background: {{ $chipBg }}; color: {{ $chipFg }};">{{ ucfirst($submission->status->value) }}</span></div>
                <div style="text-align: right;">
                    @if ($state === 'pending')
                        <span style="font-size: 12px; color: #A09889;">{{ $action }}</span>
                    @else
                        <a href="/writing" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 7px 14px; font: inherit; font-size: 12px; color: #17211E; text-decoration: none; transition: all .2s;" onmouseover="this.style.borderColor='#17211E'" onmouseout="this.style.borderColor='#E0D8CC'">{{ $action }}</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 46px; text-align: center; color: #8A8378; font-size: 13.5px; line-height: 1.6;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; color: #55605A; margin-bottom: 8px;">No submissions yet</div>
            Write your first piece in the writing studio and the AI mentor will correct it.
        </div>
    @endforelse
</div>
@endsection
