@extends('layouts.app')

@section('title', 'Lessons')

@section('crumb', 'Catalog management')

@section('content')
@php
    use App\Enums\Skill;
    use App\Enums\CefrLevel;

    $skills = Skill::cases();
    $levels = CefrLevel::cases();
@endphp

<div style="animation: fadein .4s both;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div style="font-size: 13px; color: #8A8378;">{{ $lessons->count() }} {{ Str::plural('lesson', $lessons->count()) }} in catalog · admin only</div>
        <button id="fp-toggle-form" type="button" style="border: 0; border-radius: 999px; padding: 11px 20px; background: #17211E; color: #EFEAE2; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">+ New lesson</button>
    </div>

    <div id="fp-lesson-form" style="display: none;">
        <form method="POST" action="{{ route('admin.lessons.store') }}" style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 26px; margin-bottom: 18px; animation: rise .4s both;">
            @csrf
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; font-weight: 700;">New lesson</div>
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-top: 18px;">
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Title</span>
                    <input name="title" value="{{ old('title') }}" required placeholder="e.g. Reported speech in the past" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Skill</span>
                    <select name="skill" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->value }}" @selected(old('skill') === $skill->value)>{{ ucfirst($skill->value) }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Level</span>
                    <select name="level" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected(old('level') === $level->value)>{{ $level->value }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label style="display: block; margin-top: 14px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Lesson content</span>
                <textarea name="content" rows="9" required placeholder="Write the full lesson explanation. Use ## for section headings, - for bullets, and > for example sentences." style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; resize: vertical; line-height: 1.6;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">{{ old('content') }}</textarea>
            </label>
            <div style="display: flex; gap: 10px; margin-top: 18px;">
                <button type="submit" style="border: 0; border-radius: 999px; padding: 12px 22px; background: #29C39F; color: #06231D; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Publish lesson</button>
                <button type="button" id="fp-cancel-form" style="border: 1px solid #2C3A35; border-radius: 999px; padding: 12px 22px; background: none; color: #A6B4AE; font: inherit; font-size: 13px; cursor: pointer; transition: border-color .2s;" onmouseover="this.style.borderColor='#A6B4AE'" onmouseout="this.style.borderColor='#2C3A35'">Cancel</button>
            </div>
        </form>
    </div>

    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; overflow: hidden;">
        <div style="display: grid; grid-template-columns: 2fr 1fr 90px 100px 170px; gap: 16px; padding: 15px 24px; border-bottom: 1px solid #EDE7DE; font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">
            <div>Title</div><div>Skill</div><div>Level</div><div>Exercises</div><div></div>
        </div>
        @forelse ($lessons as $lesson)
            <div style="display: grid; grid-template-columns: 2fr 1fr 90px 100px 170px; gap: 16px; padding: 16px 24px; border-bottom: 1px solid #F1ECE3; align-items: center; font-size: 13.5px; animation: rise .45s {{ 0.05 + $loop->index * 0.05 }}s both; transition: background .2s;" onmouseover="this.style.background='#FBF8F3'" onmouseout="this.style.background='transparent'">
                <div style="font-weight: 500;">{{ $lesson->title }}</div>
                <div><span style="font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; letter-spacing: .6px; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; background: #F1ECE3; color: #55605A;">{{ $lesson->skill->value }}</span></div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #55605A;">{{ $lesson->level->value }}</div>
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #8A8378;">{{ $lesson->quizzes_count }}</div>
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <a href="{{ route('admin.lessons.edit', $lesson) }}" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 6px 13px; font: inherit; font-size: 12px; color: #17211E; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#17211E'" onmouseout="this.style.borderColor='#E0D8CC'">Edit</a>
                    <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm('Delete this lesson?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="border: 1px solid #E0D8CC; background: none; border-radius: 999px; padding: 6px 13px; font: inherit; font-size: 12px; color: #A73E1E; cursor: pointer; transition: border-color .2s;" onmouseover="this.style.borderColor='#E0603B'" onmouseout="this.style.borderColor='#E0D8CC'">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="padding: 40px 24px; text-align: center; color: #8A8378; font-size: 13px;">No lessons yet — use “+ New lesson” to publish the first one.</div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var form = document.getElementById('fp-lesson-form');
    var toggle = document.getElementById('fp-toggle-form');
    var cancel = document.getElementById('fp-cancel-form');
    if (!form || !toggle) return;
    var open = false;
    function render() {
        form.style.display = open ? 'block' : 'none';
        toggle.textContent = open ? 'Close form' : '+ New lesson';
    }
    toggle.addEventListener('click', function () { open = !open; render(); });
    if (cancel) cancel.addEventListener('click', function () { open = false; render(); });
})();
</script>
@endsection
