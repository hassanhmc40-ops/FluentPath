@extends('layouts.app')

@section('title', 'Placement Questions')

@section('crumb', 'Catalog management')

@section('content')
<div style="animation: fadein .4s both;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div style="font-size: 13px; color: #8A8378;">{{ $placementQuestions->count() }} {{ Str::plural('question', $placementQuestions->count()) }} in catalog · admin only</div>
        <a href="{{ route('admin.placement-questions.create') }}" style="border: 0; border-radius: 999px; padding: 11px 20px; background: #17211E; color: #EFEAE2; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-block; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">+ New question</a>
    </div>

    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; overflow: hidden;">
        <div style="display: grid; grid-template-columns: 2fr 1fr 90px 170px; gap: 16px; padding: 15px 24px; border-bottom: 1px solid #EDE7DE; font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">
            <div>Question</div><div>Skill</div><div>Level</div><div></div>
        </div>
        @forelse ($placementQuestions as $question)
            <div style="display: grid; grid-template-columns: 2fr 1fr 90px 170px; gap: 16px; padding: 16px 24px; border-bottom: 1px solid #F1ECE3; align-items: center; font-size: 13.5px; animation: rise .45s {{ 0.05 + $loop->index * 0.05 }}s both; transition: background .2s;" onmouseover="this.style.background='#FBF8F3'" onmouseout="this.style.background='transparent'">
                <div style="font-weight: 500;">{{ Str::limit($question->question, 90) }}</div>
                <div><span style="font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; letter-spacing: .6px; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; background: #F1ECE3; color: #55605A;">{{ $question->skill->value }}</span></div>
                <div><span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; padding: 4px 10px; border-radius: 999px; background: #17211E; color: #29C39F;">{{ $question->level->value }}</span></div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <a href="{{ route('admin.placement-questions.edit', $question) }}" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 6px 13px; font: inherit; font-size: 12px; color: #17211E; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#17211E'" onmouseout="this.style.borderColor='#E0D8CC'">Edit</a>
                    <form method="POST" action="{{ route('admin.placement-questions.destroy', $question) }}" onsubmit="return confirm('Delete this question?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 6px 13px; font: inherit; font-size: 12px; color: #A73E1E; cursor: pointer; transition: border-color .2s;" onmouseover="this.style.borderColor='#E0603B'" onmouseout="this.style.borderColor='#E0D8CC'">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="padding: 40px 24px; text-align: center; color: #8A8378; font-size: 13px;">No placement questions yet — create the first one to use in the placement test.</div>
        @endforelse
    </div>
</div>
@endsection
