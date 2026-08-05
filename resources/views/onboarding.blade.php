@extends('layouts.app')

@section('title', 'Set up your space')
@section('crumb', 'Getting started')

@section('content')
@php
    $steps = [
        [
            'title' => 'What brings you here?',
            'sub' => 'This shapes the tone of your roadmap, not your level. Only the placement test decides that.',
            'name' => 'goal',
            'options' => $goals,
        ],
        [
            'title' => 'How much time per week?',
            'sub' => 'The AI sizes each week of the roadmap around this budget.',
            'name' => 'weekly_hours',
            'options' => $hours,
        ],
        [
            'title' => 'Where do you struggle most?',
            'sub' => 'A hint only — the placement test can and often does disagree.',
            'name' => 'struggle',
            'options' => $struggles,
        ],
    ];
@endphp
<div style="min-height: 100vh; display: grid; place-items: center; padding: 40px; background: #F6F2EC;">
    <div style="width: 100%; max-width: 660px; background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 24px; padding: 40px; animation: rise .5s both cubic-bezier(.2,.9,.2,1);">
        <form id="onb-form" method="POST" action="/onboarding">
            @csrf
            <div style="display: flex; gap: 6px; margin-bottom: 26px;">
                @foreach ([0, 1, 2] as $i)
                    <div id="dot-{{ $i }}" style="flex: 1; height: 5px; border-radius: 999px; background: #0E6B5C; transition: background .3s;"></div>
                @endforeach
            </div>
            <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #8A8378;">STEP <span id="step-num">1</span> / 3</div>
            <div id="onb-title" style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 31px; font-weight: 700; letter-spacing: -.9px; margin: 10px 0 8px;">{{ $steps[0]['title'] }}</div>
            <div id="onb-sub" style="font-size: 14px; color: #6C6A63; line-height: 1.6;">{{ $steps[0]['sub'] }}</div>

            @foreach ($steps as $stepIndex => $step)
                <div class="onb-step" data-step="{{ $stepIndex }}" data-title="{{ $step['title'] }}" data-sub="{{ $step['sub'] }}" style="display: {{ $stepIndex === 0 ? 'block' : 'none' }};">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 26px;">
                        @foreach ($step['options'] as $title => $note)
                            <button type="button" class="onb-opt" data-value="{{ $title }}" style="text-align: left; padding: 18px; border-radius: 15px; cursor: pointer; font: inherit; transition: all .22s; border: 1.5px solid #E5DDD2; background: #FFFEFC;" onmouseover="this.style.transform='translateY(-3px)';this.style.borderColor='#0E6B5C'" onmouseout="if(!this.classList.contains('picked')){this.style.transform='none';this.style.borderColor='#E5DDD2'}">
                                <div style="font-size: 14.5px; font-weight: 600;">{{ $title }}</div>
                                <div style="font-size: 12.5px; color: #6C6A63; margin-top: 5px; line-height: 1.5;">{{ $note }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <input type="hidden" name="goal" id="in-goal">
            <input type="hidden" name="weekly_hours" id="in-hours">
            <input type="hidden" name="struggle" id="in-struggle">

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
                <button type="button" id="onb-back" style="border: 0; background: none; font: inherit; font-size: 13px; color: #8A8378; cursor: pointer;">← Back</button>
                <button type="button" id="onb-next" style="border: 0; border-radius: 999px; padding: 13px 26px; background: #0E6B5C; color: #F2FBF8; font: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">Continue →</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const steps = document.querySelectorAll('.onb-step');
    const dots = [document.getElementById('dot-0'), document.getElementById('dot-1'), document.getElementById('dot-2')];
    const num = document.getElementById('step-num');
    const titleEl = document.getElementById('onb-title');
    const subEl = document.getElementById('onb-sub');
    const back = document.getElementById('onb-back');
    const next = document.getElementById('onb-next');
    const form = document.getElementById('onb-form');
    const stepNames = ['goal', 'weekly_hours', 'struggle'];
    const fields = { goal: 'in-goal', weekly_hours: 'in-hours', struggle: 'in-struggle' };
    let step = 0;
    const picked = {};

    document.querySelectorAll('.onb-opt').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name = stepNames[Number(btn.closest('.onb-step').dataset.step)];
            document.querySelectorAll('.onb-step[data-step="' + btn.closest('.onb-step').dataset.step + '"] .onb-opt').forEach(function (o) {
                o.classList.remove('picked');
                o.style.borderColor = '#E5DDD2';
                o.style.background = '#FFFEFC';
            });
            btn.classList.add('picked');
            btn.style.borderColor = '#0E6B5C';
            btn.style.background = '#F1F8F5';
            picked[name] = btn.dataset.value;
        });
    });

    function render() {
        steps.forEach(function (s, i) { s.style.display = i === step ? 'block' : 'none'; });
        dots.forEach(function (d, i) { d.style.background = i <= step ? '#0E6B5C' : '#E0D8CC'; });
        num.textContent = step + 1;
        titleEl.textContent = steps[step].dataset.title;
        subEl.textContent = steps[step].dataset.sub;
        back.style.visibility = step === 0 ? 'hidden' : 'visible';
        next.textContent = step === 2 ? 'Start the placement test →' : 'Continue →';
    }

    next.addEventListener('click', function () {
        if (!picked[stepNames[step]]) { next.style.opacity = '.5'; return; }
        next.style.opacity = '1';
        if (step < 2) { step++; render(); return; }
        Object.keys(fields).forEach(function (k) {
            if (picked[k]) document.getElementById(fields[k]).value = picked[k];
        });
        form.submit();
    });

    back.addEventListener('click', function () { if (step > 0) { step--; render(); } });
    render();
})();
</script>
@endsection
