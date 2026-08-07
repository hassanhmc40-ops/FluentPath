@extends('layouts.app')

@section('title', 'Edit Exercise')

@section('crumb', 'Catalog management')

@section('content')
<div style="animation: fadein .4s both;">
    <div style="max-width: 720px;">
        <div style="font-size: 13px; color: #8A8378; margin-bottom: 18px;">Editing exercise · {{ $quiz->lesson?->level->value ?? '' }} · {{ $quiz->lesson?->title ?? '' }}</div>

        <form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}" style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 28px;">
            @csrf
            @method('PUT')
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700;">Edit exercise</div>

            <label style="display: block; margin-top: 22px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Lesson</span>
                <select name="lesson_id" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                    @foreach ($lessons as $lesson)
                        <option value="{{ $lesson->id }}" @selected(old('lesson_id', $quiz->lesson_id) == $lesson->id)>{{ $lesson->title }} · {{ $lesson->level->value }} · {{ ucfirst($lesson->skill->value) }}</option>
                    @endforeach
                </select>
                @error('lesson_id')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <label style="display: block; margin-top: 16px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Title</span>
                <input name="title" value="{{ old('title', $quiz->title) }}" required placeholder="e.g. Modal verbs in formal writing" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                @error('title')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <label style="display: block; margin-top: 16px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Description</span>
                <textarea name="description" rows="3" placeholder="Optional short description for this exercise" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; resize: vertical; line-height: 1.6;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">{{ old('description', $quiz->description) }}</textarea>
                @error('description')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px;">
                <button type="submit" style="border: 0; border-radius: 999px; padding: 13px 24px; background: #29C39F; color: #06231D; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Save changes</button>
                <a href="{{ route('admin.quizzes.index') }}" style="border: 1px solid #2C3A35; border-radius: 999px; padding: 12px 22px; background: none; color: #A6B4AE; font: inherit; font-size: 13px; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#A6B4AE'" onmouseout="this.style.borderColor='#2C3A35'">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
