@extends('layouts.app')

@section('title', $quiz->title)

@section('crumb', 'Practice')

@section('content')
@php
    $skillLabel = strtoupper($quiz->lesson?->skill->value ?? 'exercise');
    $total = $quiz->quizQuestions->count();
    $questionsJson = $quiz->quizQuestions->map(fn ($q) => [
        'id' => $q->id,
        'text' => $q->question,
        'options' => [
            'a' => $q->option_a,
            'b' => $q->option_b,
            'c' => $q->option_c,
            'd' => $q->option_d,
        ],
        'correct' => $q->correct_answer,
    ])->values();
@endphp

<div style="max-width: 720px; animation: fadein .4s both;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; padding: 4px 10px; border-radius: 999px; background: #F1ECE3; color: #55605A;">{{ $total }} items</span>
            @if ($lastScore !== null)
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; padding: 4px 10px; border-radius: 999px; background: #E3F2EE; color: #0A5347;">Last: {{ (int) round((float) $lastScore) }}%</span>
            @endif
        </div>
        @if ($attempts->isNotEmpty())
            <span style="font-size: 12px; color: #8A8378;">{{ $attempts->count() }} {{ Str::plural('attempt', $attempts->count()) }}</span>
        @endif
    </div>

    @if (session('success'))
        <div style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 46px; text-align: center; animation: rise .5s both;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 58px; font-weight: 800; color: #29C39F; line-height: 1;">{{ (int) round((float) $lastScore) }}%</div>
            <div style="font-size: 14.5px; color: #A6B4AE; margin-top: 12px;">Exercise recorded. The recommendation engine has re-ranked your next topics.</div>
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 26px;">
                <a href="/quizzes/{{ $quiz->id }}" style="border: 1px solid #2C3A35; background: none; color: #EFEAE2; border-radius: 999px; padding: 12px 22px; font: inherit; font-size: 13px; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#29C39F'" onmouseout="this.style.borderColor='#2C3A35'">Try again</a>
                <a href="/writing" style="border: 0; background: #29C39F; color: #06231D; border-radius: 999px; padding: 12px 22px; font: inherit; font-size: 13px; font-weight: 600; text-decoration: none;">Next: writing task</a>
            </div>
        </div>
    @elseif ($total === 0)
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 46px; text-align: center; color: #8A8378; font-size: 13.5px; line-height: 1.6;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; color: #55605A; margin-bottom: 8px;">No questions yet</div>
            This exercise has no items yet. Check back soon.
        </div>
    @else
        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 30px; animation: rise .5s both;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #8A8378;">ITEM <span id="ex-num">1</span> / {{ $total }} · {{ $skillLabel }}</div>
                <div style="font-size: 12.5px; color: #0E6B5C; font-weight: 500;"><span id="ex-score">0</span> correct</div>
            </div>
            <div style="height: 6px; border-radius: 999px; background: #ECE5DA; margin-top: 16px; overflow: hidden;">
                <div id="ex-progress" style="height: 100%; background: #29C39F; border-radius: 999px; width: 0%; transition: width .4s cubic-bezier(.2,.9,.2,1);"></div>
            </div>
            <div id="ex-text" style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 25px; font-weight: 700; letter-spacing: -.5px; line-height: 1.3; margin: 26px 0 22px;"></div>
            <div id="ex-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;"></div>
            <div id="ex-feedback" style="display: none; margin-top: 18px; padding: 15px 18px; border-radius: 13px; font-size: 13.5px; line-height: 1.6; animation: rise .35s both;"></div>
        </div>
    @endif

    @if ($attempts->isNotEmpty())
        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; overflow: hidden; margin-top: 22px; animation: rise .5s .1s both;">
            <div style="display: grid; grid-template-columns: 1fr 120px; gap: 16px; padding: 15px 24px; border-bottom: 1px solid #EDE7DE; font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">
                <div>Date</div><div style="text-align: right;">Score</div>
            </div>
            @foreach ($attempts as $attempt)
                <div style="display: grid; grid-template-columns: 1fr 120px; gap: 16px; padding: 15px 24px; border-bottom: 1px solid #F1ECE3; align-items: center; font-size: 13.5px; animation: rise .45s {{ 0.12 + $loop->index * 0.05 }}s both;">
                    <div style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; color: #8A8378;">{{ $attempt->completed_at->format('M j, Y · H:i') }}</div>
                    <div style="text-align: right;"><span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; padding: 4px 10px; border-radius: 999px; background: #E3F2EE; color: #0A5347;">{{ (int) round((float) $attempt->score) }}%</span></div>
                </div>
            @endforeach
        </div>
    @endif

    <form id="quiz-form" method="POST" action="/quizzes/{{ $quiz->id }}/attempt">
        @csrf
        <span id="quiz-answers"></span>
    </form>
</div>
@endsection

@section('scripts')
@if ($total > 0 && ! session('success'))
<script>
const QUESTIONS = @json($questionsJson);
const TOTAL = QUESTIONS.length;
let exIdx = 0, exScore = 0, exAnswered = 0;
const exAnswers = [];

const LETTERS = ['a', 'b', 'c', 'd'];

function exRender() {
    const q = QUESTIONS[exIdx];
    document.getElementById('ex-num').textContent = exIdx + 1;
    document.getElementById('ex-score').textContent = exScore;
    document.getElementById('ex-progress').style.width = (exAnswered / TOTAL * 100) + '%';
    document.getElementById('ex-text').textContent = q.text;

    const wrap = document.getElementById('ex-options');
    wrap.innerHTML = '';
    LETTERS.forEach(letter => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.style.cssText = 'text-align:left; padding:15px 18px; border-radius:13px; cursor:pointer; font:inherit; font-size:14.5px; transition:all .2s; border:1.5px solid #E0D8CC; background:#FFFEFC; color:#17211E; display:flex; gap:12px; align-items:flex-start;';
        btn.innerHTML = '<span style="font-family:\'IBM Plex Mono\',monospace; font-size:11.5px; color:#8A8378; margin-top:2px; flex:none;">' + letter.toUpperCase() + '</span><span style="flex:1;">' + q.options[letter] + '</span>';
        btn.onmouseover = () => { btn.style.transform = 'translateY(-3px)'; btn.style.borderColor = '#0E6B5C'; };
        btn.onmouseout = () => { btn.style.transform = 'none'; };
        btn.onclick = () => exPick(letter, btn);
        wrap.appendChild(btn);
    });

    const fb = document.getElementById('ex-feedback');
    fb.style.display = 'none';
}

function exPick(letter, btn) {
    const q = QUESTIONS[exIdx];
    const right = letter === q.correct;

    exAnswers.push({ quiz_question_id: q.id, selected_option: letter });
    exAnswered++;
    if (right) exScore++;

    const buttons = document.querySelectorAll('#ex-options button');
    buttons.forEach(b => {
        b.style.pointerEvents = 'none';
        const l = b.textContent.trim().charAt(0).toLowerCase();
        if (l === q.correct) {
            b.style.border = '1.5px solid #0E6B5C';
            b.style.background = '#E3F2EE';
        } else if (l === letter) {
            b.style.border = '1.5px solid #E0603B';
            b.style.background = '#FBE7DF';
        }
    });

    const fb = document.getElementById('ex-feedback');
    fb.style.display = 'block';
    if (right) {
        fb.style.background = '#E3F2EE';
        fb.style.color = '#0A5347';
    } else {
        fb.style.background = '#FBE7DF';
        fb.style.color = '#A73E1E';
    }

    const last = exIdx === TOTAL - 1;
    const hint = right
        ? 'Correct — well done.'
        : 'Not quite. The right answer is ' + q.correct.toUpperCase() + '.';
    fb.innerHTML = '<span>' + hint + '</span> <button type="button" onclick="exNext()" style="margin-left:10px; border:0; border-radius:999px; padding:9px 18px; background:#17211E; color:#EFEAE2; font:inherit; font-size:12.5px; font-weight:600; cursor:pointer;">' + (last ? 'Submit' : 'Next') + ' →</button>';
}

function exNext() {
    if (exIdx >= TOTAL - 1) {
        const host = document.getElementById('quiz-answers');
        exAnswers.forEach((a, i) => {
            const qid = document.createElement('input');
            qid.type = 'hidden';
            qid.name = 'answers[' + i + '][quiz_question_id]';
            qid.value = a.quiz_question_id;
            host.appendChild(qid);
            const opt = document.createElement('input');
            opt.type = 'hidden';
            opt.name = 'answers[' + i + '][selected_option]';
            opt.value = a.selected_option;
            host.appendChild(opt);
        });
        document.getElementById('quiz-form').submit();
        return;
    }
    exIdx++;
    exRender();
}

exRender();
</script>
@endif
@endsection
