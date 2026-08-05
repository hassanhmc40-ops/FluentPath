@extends('layouts.app')

@section('title', 'Exercises')

@section('crumb', 'Catalog management')

@section('content')
<div style="animation: fadein .4s both;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <div style="font-size: 13px; color: #8A8378;">{{ $quizzes->count() }} {{ Str::plural('exercise', $quizzes->count()) }} in catalog · admin only</div>
        <a href="{{ route('admin.quizzes.create') }}" style="border: 0; border-radius: 999px; padding: 11px 20px; background: #17211E; color: #EFEAE2; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-block; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">+ New exercise</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px;">
        @forelse ($quizzes as $quiz)
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 22px; animation: rise .5s {{ 0.05 + $loop->index * 0.06 }}s both; transition: transform .25s, box-shadow .25s;" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 18px 38px rgba(23,33,30,.1)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">{{ $quiz->lesson?->skill ? ucfirst($quiz->lesson->skill->value) : 'Exercise' }}</span>
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #F1ECE3; color: #55605A;">{{ $quiz->quiz_questions_count }} {{ Str::plural('item', $quiz->quiz_questions_count) }}</span>
                </div>
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700; margin-top: 10px; line-height: 1.3;">{{ $quiz->title }}</div>
                <div style="font-size: 12.5px; color: #8A8378; margin-top: 8px;">Linked to: {{ $quiz->lesson?->title ?? '—' }}</div>
                <div style="display: flex; gap: 8px; margin-top: 16px;">
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 7px 14px; font: inherit; font-size: 12px; color: #17211E; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#17211E'" onmouseout="this.style.borderColor='#E0D8CC'">Edit</a>
                    <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Delete this exercise?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 7px 14px; font: inherit; font-size: 12px; color: #A73E1E; cursor: pointer; transition: border-color .2s;" onmouseover="this.style.borderColor='#E0603B'" onmouseout="this.style.borderColor='#E0D8CC'">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 18px; padding: 44px 24px; text-align: center; color: #8A8378; font-size: 13px; animation: rise .4s both;">No exercises yet — create the first one to attach questions to a lesson.</div>
        @endforelse
    </div>
</div>
@endsection
