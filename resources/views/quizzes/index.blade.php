@extends('layouts.app')

@section('title', 'Exercises')

@section('crumb', 'Practice')

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px; animation: fadein .4s both;">
    @forelse ($quizzes as $quiz)
        @php
            $skill = $quiz->lesson?->skill->value ?? 'grammar';
            $lastScore = $lastAttempts[$quiz->id]->score ?? null;
        @endphp
        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 22px; display: flex; flex-direction: column; animation: rise .5s {{ 0.05 + $loop->index * 0.06 }}s both cubic-bezier(.2,.9,.2,1); transition: transform .26s, box-shadow .26s;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 18px 38px rgba(23,33,30,.1)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #0E6B5C;">{{ ucfirst($skill) }}</span>
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #F1ECE3; color: #55605A;">{{ $quiz->quiz_questions_count }} items</span>
            </div>
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700; margin-top: 10px; line-height: 1.3;">{{ $quiz->title }}</div>
            <div style="font-size: 12.5px; color: #8A8378; margin-top: 8px;">Linked to: {{ $quiz->lesson->title ?? 'General' }}</div>
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 16px;">
                @if ($lastScore !== null)
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; padding: 4px 10px; border-radius: 999px; background: #E3F2EE; color: #0A5347;">Last: {{ (int) round((float) $lastScore) }}%</span>
                @else
                    <span style="font-size: 12px; color: #8A8378;">Not started</span>
                @endif
                <a href="/quizzes/{{ $quiz->id }}" style="border: 1px solid #17211E; border-radius: 999px; padding: 8px 16px; background: #FFFDFA; font: inherit; font-size: 12.5px; font-weight: 500; color: #17211E; text-decoration: none; transition: all .2s;" onmouseover="this.style.background='#17211E';this.style.color='#EFEAE2'" onmouseout="this.style.background='#FFFDFA';this.style.color='#17211E'">{{ $lastScore !== null ? 'Retry' : 'Start exercise' }} →</a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 46px; text-align: center; color: #8A8378; font-size: 13.5px; line-height: 1.6;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; color: #55605A; margin-bottom: 8px;">No exercises yet</div>
            Exercises will appear here as the catalog grows. Start with a lesson to unlock its drills.
        </div>
    @endforelse
</div>
@endsection
