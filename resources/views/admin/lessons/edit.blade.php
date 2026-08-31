@extends('layouts.app')

@section('title', 'Edit Lesson')

@section('crumb', 'Catalog management')

@section('content')
@php
    use App\Enums\Skill;
    use App\Enums\CefrLevel;

    $skills = Skill::cases();
    $levels = CefrLevel::cases();
@endphp

<div style="animation: fadein .4s both;">
    <div style="max-width: 720px;">
        <div style="font-size: 13px; color: #8A8378; margin-bottom: 18px;">Editing lesson · {{ $lesson->level->value }} · {{ $lesson->skill->value }}</div>

        <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 28px;">
            @csrf
            @method('PUT')
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700;">Edit lesson</div>

            <div class="fp-form-grid-3" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-top: 22px;">
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Title</span>
                    <input name="title" value="{{ old('title', $lesson->title) }}" required placeholder="e.g. Reported speech in the past" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Skill</span>
                    <select name="skill" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->value }}" @selected(old('skill', $lesson->skill->value) === $skill->value)>{{ ucfirst($skill->value) }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Level</span>
                    <select name="level" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected(old('level', $lesson->level->value) === $level->value)>{{ $level->value }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label style="display: block; margin-top: 16px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Lesson content</span>
                <textarea name="content" rows="14" required placeholder="Write the full lesson explanation. Use ## for section headings, - for bullets, and > for example sentences." style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; resize: vertical; line-height: 1.6;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">{{ old('content', $lesson->content) }}</textarea>
            </label>

            <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px;">
                <button type="submit" style="border: 0; border-radius: 999px; padding: 13px 24px; background: #29C39F; color: #06231D; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Save changes</button>
                <a href="{{ route('admin.lessons.index') }}" style="border: 1px solid #2C3A35; border-radius: 999px; padding: 12px 22px; background: none; color: #A6B4AE; font: inherit; font-size: 13px; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#A6B4AE'" onmouseout="this.style.borderColor='#2C3A35'">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection