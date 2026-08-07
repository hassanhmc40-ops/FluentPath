@extends('layouts.app')

@section('title', 'New Exercise Question')

@section('crumb', 'Catalog management')

@section('content')
<div style="animation: fadein .4s both;">
    <div style="max-width: 720px;">
        <div style="font-size: 13px; color: #8A8378; margin-bottom: 18px;">New exercise question</div>

        <form method="POST" action="{{ route('admin.quiz-questions.store') }}" style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 28px;">
            @csrf
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700;">Publish a new question</div>

            <label style="display: block; margin-top: 22px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Exercise</span>
                <select name="quiz_id" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                    @foreach ($quizzes as $quiz)
                        <option value="{{ $quiz->id }}" @selected(old('quiz_id') == $quiz->id)>{{ $quiz->title }} — {{ $quiz->lesson?->title ?? 'no lesson' }}</option>
                    @endforeach
                </select>
                @error('quiz_id')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <label style="display: block; margin-top: 16px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Question</span>
                <textarea name="question" rows="3" required placeholder="Write the question stem here" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; resize: vertical; line-height: 1.6;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">{{ old('question') }}</textarea>
                @error('question')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 16px;">
                @foreach (['a', 'b', 'c', 'd'] as $option)
                    <label style="display: block;">
                        <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Option {{ strtoupper($option) }}</span>
                        <input name="option_{{ $option }}" value="{{ old('option_' . $option) }}" required placeholder="Option {{ strtoupper($option) }}" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                    </label>
                @endforeach
            </div>

            <label style="display: block; margin-top: 16px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Correct answer</span>
                <select name="correct_answer" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                    @foreach (['a', 'b', 'c', 'd'] as $option)
                        <option value="{{ $option }}" @selected(old('correct_answer') === $option)>{{ strtoupper($option) }}</option>
                    @endforeach
                </select>
                @error('correct_answer')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px;">
                <button type="submit" style="border: 0; border-radius: 999px; padding: 13px 24px; background: #29C39F; color: #06231D; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Publish question</button>
                <a href="{{ route('admin.quiz-questions.index') }}" style="border: 1px solid #2C3A35; border-radius: 999px; padding: 12px 22px; background: none; color: #A6B4AE; font: inherit; font-size: 13px; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#A6B4AE'" onmouseout="this.style.borderColor='#2C3A35'">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
