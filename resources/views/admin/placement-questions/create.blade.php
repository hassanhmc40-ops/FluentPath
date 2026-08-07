@extends('layouts.app')

@section('title', 'New Placement Question')

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
        <div style="font-size: 13px; color: #8A8378; margin-bottom: 18px;">New placement question</div>

        <form method="POST" action="{{ route('admin.placement-questions.store') }}" style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 28px;">
            @csrf
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700;">Publish a new placement question</div>

            <label style="display: block; margin-top: 22px;">
                <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Question</span>
                <textarea name="question" rows="7" required maxlength="2000" placeholder="Write the question prompt for the placement test." style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; resize: vertical; line-height: 1.6;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">{{ old('question') }}</textarea>
                @error('question')
                    <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                @enderror
            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 16px;">
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">Skill</span>
                    <select name="skill" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach ($skills as $skill)
                            <option value="{{ $skill->value }}" @selected(old('skill') === $skill->value)>{{ ucfirst($skill->value) }}</option>
                        @endforeach
                    </select>
                    @error('skill')
                        <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 7px;">CEFR level</span>
                    <select name="level" required style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach ($levels as $level)
                            <option value="{{ $level->value }}" @selected(old('level') === $level->value)>{{ $level->value }}</option>
                        @endforeach
                    </select>
                    @error('level')
                        <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </label>
            </div>

            <div id="mcq-fields" style="margin-top: 20px; display: none;">
                <div style="font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089; margin-bottom: 10px;">Answer options (multiple choice)</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    @foreach (['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C', 'd' => 'Option D'] as $letter => $label)
                        <label style="display: block;">
                            <span style="display: block; font-size: 10px; letter-spacing: 1.2px; text-transform: uppercase; color: #7E9089; margin-bottom: 6px;">{{ $label }}</span>
                            <input type="text" name="option_{{ $letter }}" value="{{ old('option_' . $letter) }}" maxlength="255" placeholder="{{ $label }} text" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 11px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                            @error('option_' . $letter)
                                <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                            @enderror
                        </label>
                    @endforeach
                </div>
                <label style="display: block; margin-top: 12px; max-width: 340px;">
                    <span style="display: block; font-size: 10px; letter-spacing: 1.2px; text-transform: uppercase; color: #7E9089; margin-bottom: 6px;">Correct answer</span>
                    <select name="correct_answer" style="width: 100%; border: 1px solid #2C3A35; border-radius: 11px; padding: 11px 14px; font-size: 14px; background: #202D29; color: #EFEAE2; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#29C39F'" onblur="this.style.borderColor='#2C3A35'">
                        @foreach (['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C', 'd' => 'Option D'] as $letter => $label)
                            <option value="{{ $letter }}" @selected(old('correct_answer') === $letter)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('correct_answer')
                        <div style="color: #E0603B; font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </label>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; margin-top: 22px;">
                <button type="submit" style="border: 0; border-radius: 999px; padding: 13px 24px; background: #29C39F; color: #06231D; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Publish question</button>
                <a href="{{ route('admin.placement-questions.index') }}" style="border: 1px solid #2C3A35; border-radius: 999px; padding: 12px 22px; background: none; color: #A6B4AE; font: inherit; font-size: 13px; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#A6B4AE'" onmouseout="this.style.borderColor='#2C3A35'">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var skill = document.querySelector('select[name="skill"]');
        var mcq = document.getElementById('mcq-fields');
        if (!skill || !mcq) return;
        var toggle = function () {
            mcq.style.display = skill.value === 'writing' ? 'none' : 'block';
        };
        skill.addEventListener('change', toggle);
        toggle();
    })();
</script>
@endsection
