@extends('layouts.app')

@section('title', $lesson->title)

@section('crumb', 'Lessons')

@section('content')
@php
    $skill = $lesson->skill->value;
    $level = $lesson->level->value;
    $levelLetter = strtoupper(substr($level, 0, 1));

    $sections = [
        'grammar' => [
            ['h' => 'Why this matters', 'p' => 'Grammar is the engine of meaning. Getting the core patterns right lets you say exactly what you mean — and lets your reader follow you without guessing.', 'example' => null],
            ['h' => 'The pattern in focus', 'p' => 'This lesson breaks the structure into small steps: form first, then use, then common traps. Each step builds on the previous one.', 'example' => 'She has been working here since March. — present perfect continuous for an action that started in the past and still continues.'],
            ['h' => 'Common mistakes at this level', 'p' => 'Learners at this level often mix two similar patterns. The examples below show the difference side by side.', 'example' => '"I am agree with you." → "I agree with you." — stative verbs do not take the continuous'],
        ],
        'vocabulary' => [
            ['h' => 'Why this matters', 'p' => 'A wider vocabulary lets you express shades of meaning instead of reaching for the same three words.', 'example' => null],
            ['h' => 'Words in context', 'p' => 'You will meet each word inside a natural sentence, so you learn how it behaves — not just what it means.', 'example' => 'The meeting was postponed until Friday. — "postpone" means to move something to a later time.'],
            ['h' => 'Collocations', 'p' => 'Words travel in pairs. Learning the right partner words makes you sound natural and confident.', 'example' => 'make a decision (not "do a decision") · strong coffee (not "powerful coffee")'],
        ],
        'reading' => [
            ['h' => 'Why this matters', 'p' => 'Reading trains your eye to recognise grammar and vocabulary in real use — the fastest way to make them automatic.', 'example' => null],
            ['h' => 'Read with purpose', 'p' => 'Before you read, ask what you are looking for: a fact, an opinion, a sequence of events. That focus doubles your comprehension.', 'example' => 'Skim the first line of each paragraph, then scan for the detail you need.'],
            ['h' => 'Clue words', 'p' => 'Connectors and signal words tell you what comes next: contrast, cause, or time order.', 'example' => '"However" signals a contrast · "as a result" signals a consequence · "meanwhile" signals parallel time'],
        ],
        'writing' => [
            ['h' => 'Why this matters', 'p' => 'Writing is thinking in slow motion. It shows you exactly which patterns you own and which ones are still wobbly.', 'example' => null],
            ['h' => 'Structure your answer', 'p' => 'A strong paragraph has one clear idea, a reason, and an example. Keep every sentence on a job.', 'example' => 'Topic sentence → supporting detail → example → link to the next idea.'],
            ['h' => 'Link your sentences', 'p' => 'Short sentences are fine, but connectors turn a list of facts into a flowing argument.', 'example' => '"I was tired. I finished the report." → "Although I was tired, I finished the report."'],
        ],
    ];

    $content = $sections[$skill] ?? $sections['grammar'];

    $whyText = match ($skill) {
        'grammar' => 'Grammar is the engine of meaning. This lesson isolates the pattern so you can practise it in the attached exercises until it becomes automatic.',
        'vocabulary' => 'Words are learned in chunks. This lesson builds the exact phrases and collocations you need at your level, ready to reuse in writing.',
        'reading' => 'Reading shows you the grammar and vocabulary you already know working together in real sentences. Practise actively and the patterns stick.',
        default => 'Writing is thinking in slow motion. This lesson gives you the structure to turn your ideas into clear, flowing English.',
    };
@endphp

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 22px; align-items: start; animation: fadein .4s both;">
    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 32px; animation: rise .5s both;">
        <a href="/lessons" style="border: 0; background: none; font: inherit; font-size: 12.5px; color: #8A8378; cursor: pointer; padding: 0; display: inline-block;">← Back to lessons</a>

        <div style="display: flex; gap: 9px; margin: 16px 0 10px; align-items: center;">
            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 12px; font-weight: 500; background: #17211E; color: #29C39F; padding: 4px 9px; border-radius: 8px;">{{ $levelLetter }}</span>
            <span style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #0E6B5C;">{{ $skill }}</span>
            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #8A8378;">{{ $level }}</span>
        </div>

        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 30px; font-weight: 700; letter-spacing: -.8px; line-height: 1.15;">{{ $lesson->title }}</div>

        <div style="display: flex; gap: 8px; align-items: center; font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #8A8378; margin-top: 10px;">
            <span>{{ ucfirst($skill) }} lesson</span>
            <span style="color: #E0D8CC;">·</span>
            <span>{{ $level }}</span>
            <span style="color: #E0D8CC;">·</span>
            <span>{{ $lesson->quizzes->count() }} {{ Str::plural('exercise', $lesson->quizzes->count()) }}</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 22px; margin-top: 28px;">
            @foreach ($content as $i => $s)
                <div style="animation: rise .5s {{ 0.1 + $i * 0.08 }}s both;">
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700;">{{ $s['h'] }}</div>
                    <div style="font-size: 14.5px; line-height: 1.75; color: #3D453F; margin-top: 8px;">{{ $s['p'] }}</div>
                    @if ($s['example'])
                        <div style="margin-top: 12px; background: #F3EFE8; border-left: 3px solid #29C39F; border-radius: 0 12px 12px 0; padding: 14px 18px; font-size: 14px; line-height: 1.6; color: #17211E;">{{ $s['example'] }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="display: flex; gap: 12px; margin-top: 32px; align-items: center;">
            @if ($completed)
                <span style="display: inline-flex; align-items: center; gap: 7px; background: #E3F2EE; color: #0A5347; border-radius: 999px; padding: 11px 20px; font-size: 13px; font-weight: 600;">Completed ✓</span>
                <a href="{{ $lesson->quizzes->isNotEmpty() ? '/quizzes/'.$lesson->quizzes->first()->id : '/quizzes' }}" style="border: 1px solid #17211E; border-radius: 999px; padding: 12px 22px; background: none; font: inherit; font-size: 13.5px; font-weight: 500; color: #17211E; text-decoration: none; transition: all .2s;" onmouseover="this.style.background='#17211E';this.style.color='#EFEAE2'" onmouseout="this.style.background='none';this.style.color='#17211E'">Review exercises →</a>
            @else
                <form method="POST" action="/lessons/{{ $lesson->id }}/complete">
                    @csrf
                    <button type="submit" style="border: 0; border-radius: 999px; padding: 13px 24px; background: #17211E; color: #EFEAE2; font: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">Mark as completed</button>
                </form>
                <a href="{{ $lesson->quizzes->isNotEmpty() ? '/quizzes/'.$lesson->quizzes->first()->id : '/quizzes' }}" style="border: 1px solid #17211E; border-radius: 999px; padding: 12px 22px; background: none; font: inherit; font-size: 13.5px; font-weight: 500; color: #17211E; text-decoration: none; transition: all .2s;" onmouseover="this.style.background='#17211E';this.style.color='#EFEAE2'" onmouseout="this.style.background='none';this.style.color='#17211E'">Practise with exercises →</a>
            @endif
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
        <div style="background: #17211E; color: #EFEAE2; border-radius: 18px; padding: 22px; animation: rise .5s .1s both;">
            <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #7E9089;">Why this lesson</div>
            <div style="font-size: 13.5px; line-height: 1.6; color: #C6D1CC; margin-top: 10px;">{{ $whyText }}</div>
        </div>

        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 22px; animation: rise .5s .16s both;">
            <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Attached exercises</div>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 13px;">
                @forelse ($lesson->quizzes as $quiz)
                    <a href="/quizzes/{{ $quiz->id }}" style="display: flex; justify-content: space-between; font-size: 13px; color: #17211E; text-decoration: none; transition: color .2s;" onmouseover="this.style.color='#0E6B5C'" onmouseout="this.style.color='#17211E'">
                        <span>{{ $quiz->title }}</span>
                        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #8A8378;">{{ $quiz->quizQuestions()->count() }} items</span>
                    </a>
                @empty
                    <div style="font-size: 13px; color: #8A8378;">No exercises attached yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
