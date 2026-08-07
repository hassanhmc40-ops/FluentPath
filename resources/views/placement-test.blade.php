@extends('layouts.app')

@section('title', 'Placement Test')
@section('crumb', 'Holistic AI evaluation')

@section('content')
<div style="max-width: 820px; animation: fadein .4s both;">

    @if ($processing)
        {{-- Analyzing state --}}
        <div style="background: #17211E; border-radius: 20px; padding: 60px; text-align: center; color: #EFEAE2; animation: fadein .3s both;">
            <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 30px;">
                <div style="position: absolute; inset: 0; animation: orbit 2.4s linear infinite;">
                    <div style="position: absolute; top: 0; left: 50%; width: 14px; height: 14px; margin-left: -7px; border-radius: 50%; background: #29C39F;"></div>
                </div>
                <div style="position: absolute; inset: 16px; animation: orbit 3.4s linear infinite reverse;">
                    <div style="position: absolute; top: 0; left: 50%; width: 10px; height: 10px; margin-left: -5px; border-radius: 50%; background: #E0603B;"></div>
                </div>
                <div style="position: absolute; inset: 40px; border-radius: 50%; background: rgba(41,195,159,.2); animation: pulse 2s infinite;"></div>
            </div>
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 25px; font-weight: 700;">Evaluating your submission</div>
            <div style="font-size: 13.5px; color: #A6B4AE; margin-top: 10px;">Auto-scoring your multiple choice parts, AI is grading your writing...</div>
            <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #7E9089; margin-top: 26px;">202 ACCEPTED · job queued · non-blocking</div>
        </div>

    @elseif ($test && $test->status->value === 'analyzed')
        {{-- Done state --}}
        @php
            $level = $test->cefr_level?->value ?? '—';
            $grammarScore = $test->grammar_score !== null ? round((float) $test->grammar_score) : null;
            $vocabScore = $test->vocabulary_score !== null ? round((float) $test->vocabulary_score) : null;
            $writingScore = $test->writing_score !== null ? round((float) $test->writing_score) : null;
            $strengths = $test->strengths ?? [];
            $weaknesses = $test->weaknesses ?? [];
        @endphp
        <div style="display: flex; flex-direction: column; gap: 18px; animation: rise .5s both;">
            {{-- Level card --}}
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 30px; display: flex; gap: 30px; align-items: center;">
                <div style="text-align: center;">
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 62px; font-weight: 800; letter-spacing: -2.5px; color: #0E6B5C; line-height: 1;">{{ $level }}</div>
                    <div style="font-size: 10.5px; letter-spacing: 1.6px; text-transform: uppercase; color: #8A8378; margin-top: 5px;">CEFR level</div>
                </div>
                <div style="flex: 1; font-size: 14.5px; line-height: 1.65; color: #3D453F; text-wrap: pretty;">
                    Your placement test has been evaluated. Your strengths and areas for improvement are listed below. Your 4-week roadmap is ready.
                </div>
            </div>

            {{-- Per-skill scores --}}
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;">
                    @foreach ([
                        ['name' => 'Grammar', 'score' => $grammarScore, 'color' => '#0E6B5C'],
                        ['name' => 'Vocabulary', 'score' => $vocabScore, 'color' => '#17211E'],
                        ['name' => 'Writing', 'score' => $writingScore, 'color' => '#E0603B'],
                        ['name' => 'Reading', 'score' => $test->reading_score !== null ? round((float) $test->reading_score) : null, 'color' => '#0E6B5C'],
                    ] as $i => $skill)
                    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px; animation: rise .5s {{ 0.1 + ($i * 0.07) }}s both;">
                        <div style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">{{ $skill['name'] }}</div>
                        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 27px; font-weight: 700; margin-top: 6px; color: {{ $skill['color'] }};">{{ $skill['score'] !== null ? $skill['score'] : '—' }}</div>
                    </div>
                @endforeach
            </div>

            {{-- Strengths / Weaknesses --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 24px;">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #0E6B5C;">Strengths</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px;">
                        @forelse ($strengths as $i => $s)
                            <span style="padding: 7px 13px; border-radius: 999px; background: #E3F2EE; color: #0A5347; font-size: 12.5px; animation: rise .5s {{ 0.15 + ($i * 0.1) }}s both;">{{ $s }}</span>
                        @empty
                            <span style="padding: 7px 13px; border-radius: 999px; background: #E3F2EE; color: #0A5347; font-size: 12.5px;">—</span>
                        @endforelse
                    </div>
                </div>
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 24px;">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #E0603B;">Weaknesses</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px;">
                        @forelse ($weaknesses as $i => $w)
                            <span style="padding: 7px 13px; border-radius: 999px; background: #FBE7DF; color: #A73E1E; font-size: 12.5px; animation: rise .5s {{ 0.15 + ($i * 0.1) }}s both;">{{ $w }}</span>
                        @empty
                            <span style="padding: 7px 13px; border-radius: 999px; background: #FBE7DF; color: #A73E1E; font-size: 12.5px;">—</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <a href="/roadmap" style="border: 0; border-radius: 999px; padding: 13px 26px; background: #0E6B5C; color: #F2FBF8; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">View my roadmap →</a>
                <form method="POST" action="/placement-test/retake">
                    @csrf
                    <button type="submit" style="border: 1px solid #E0603B; background: none; color: #A73E1E; border-radius: 999px; padding: 12px 24px; font: inherit; font-size: 13px; cursor: pointer; transition: all .2s;" onmouseover="this.style.background='#E0603B';this.style.color='#FFF6F2'" onmouseout="this.style.background='none';this.style.color='#A73E1E'">Retake test</button>
                </form>
            </div>
        </div>

    @else
        {{-- Intro / form state --}}
        @php
            $skills = ['grammar' => 'Grammar', 'vocabulary' => 'Vocabulary', 'reading' => 'Reading', 'writing' => 'Writing'];
            $grouped = $questions->groupBy(fn ($q) => $q->skill->value);
            $partMeta = [
                1 => ['name' => 'Grammar', 'note' => $grouped->get('grammar', collect())->count() . ' items on tense and condition.'],
                2 => ['name' => 'Vocabulary', 'note' => $grouped->get('vocabulary', collect())->count() . ' items on meaning and collocation.'],
                3 => ['name' => 'Reading', 'note' => $grouped->get('reading', collect())->count() . ' items on comprehension.'],
                4 => ['name' => 'Writing', 'note' => $grouped->get('writing', collect())->count() . ' free texts, 60–100 words each.'],
            ];
        @endphp

        {{-- Hero intro card --}}
        <div style="background: #17211E; color: #EFEAE2; border-radius: 22px; padding: 40px; position: relative; overflow: hidden; animation: rise .5s both cubic-bezier(.2,.9,.2,1);">
            <div style="position: absolute; right: -80px; top: -80px; width: 280px; height: 280px; border-radius: 50%; background: radial-gradient(circle, rgba(41,195,159,.3), transparent 70%); animation: drift 15s ease-in-out infinite;"></div>
            <div style="position: relative;">
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; letter-spacing: 1.4px; color: #29C39F;">FIRST-TIME DIAGNOSTIC · REQUIRED ONCE</div>
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 38px; font-weight: 800; letter-spacing: -1.4px; line-height: 1.1; margin: 12px 0 10px; max-width: 520px; text-wrap: pretty;">Before anything else, we measure each part separately.</div>
                <div style="font-size: 14.5px; color: #A6B4AE; line-height: 1.65; max-width: 520px;">Four parts, taken in order. Grammar, vocabulary and reading are multiple choice and scored automatically; the AI grades your writing the way a teacher would, then sets your CEFR level and builds the roadmap. Skip any question you don't know — it simply counts as incorrect.</div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 28px 0 30px;">
                    @foreach ($partMeta as $n => $meta)
                        <div style="background: #202D29; border: 1px solid #2C3A35; border-radius: 15px; padding: 18px; animation: rise .5s {{ 0.1 + ($loop->index * 0.07) }}s both;">
                            <div style="font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; color: #61726C;">PART {{ $n }}</div>
                            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 16px; font-weight: 700; margin-top: 7px;">{{ $meta['name'] }}</div>
                            <div style="font-size: 12px; color: #7E9089; margin-top: 6px; line-height: 1.5;">{{ $meta['note'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="/placement-test" id="placement-form" style="margin-top: 20px; display: flex; flex-direction: column; gap: 20px;">
            @csrf

            @php
                $partNumber = 0;
                $partSkillOrder = ['grammar', 'vocabulary', 'reading', 'writing'];
                $partLabels = ['grammar' => 'Grammar', 'vocabulary' => 'Vocabulary', 'reading' => 'Reading', 'writing' => 'Writing'];
                $questionIndex = 0;
            @endphp

            @foreach ($partSkillOrder as $skillKey)
                @php
                    $partNumber++;
                    $partQuestions = $questions->filter(fn ($q) => $q->skill->value === $skillKey)->values();
                    $partNote = $partMeta[$partNumber]['note'];
                    $activeBg = '#17211E';
                    $trackBg = '#ECE5DA';
                    $barBg = '#29C39F';
                @endphp

                {{-- Part header --}}
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .55s {{ ($partNumber - 1) * 0.12 }}s both cubic-bezier(.2,.9,.2,1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 18px;">
                        <div>
                            <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #8A8378;">PART {{ $partNumber }} · {{ strtoupper($skillKey) }}</div>
                            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700; margin-top: 4px;">{{ $partLabels[$skillKey] }}</div>
                        </div>
                        <div style="font-size: 11.5px; color: #A09889;">{{ $partQuestions->count() }} {{ Str::plural('item', $partQuestions->count()) }}</div>
                    </div>
                    <div style="height: 4px; border-radius: 999px; background: {{ $trackBg }}; overflow: hidden; margin-bottom: 24px;">
                        <div style="height: 100%; border-radius: 999px; background: {{ $barBg }}; width: 100%;"></div>
                    </div>

                    @if ($skillKey === 'writing')
                        {{-- Writing prompt --}}
                        @foreach ($partQuestions as $q)
                            <div data-q="{{ $questionIndex }}">
                                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700; letter-spacing: -.4px; line-height: 1.3; margin-bottom: 16px; text-wrap: pretty;">{{ $q->question }}</div>
                                <textarea
                                    name="answers[{{ $questionIndex }}][answer]"
                                    placeholder="Write 60–100 words. Spelling and tense choices are all part of the assessment."
                                    style="width: 100%; min-height: 190px; resize: vertical; border: 1px solid #E0D8CC; border-radius: 14px; padding: 16px; font: inherit; font-size: 14.5px; line-height: 1.7; background: #FFFEFC; color: #17211E; outline: none; transition: border-color .2s, box-shadow .2s;"
                                    onfocus="this.style.borderColor='#0E6B5C';this.style.boxShadow='0 0 0 4px rgba(14,107,92,.1)'"
                                    onblur="this.style.borderColor='#E0D8CC';this.style.boxShadow='none'"
                                ></textarea>
                                <button type="button" data-skip="{{ $questionIndex }}" style="border:1px solid #E0D8CC; background:#FFFEFC; border-radius:999px; padding:7px 16px; font:inherit; font-size:12.5px; color:#8A8378; cursor:pointer; transition:border-color .2s, color .2s; margin-top:12px;" onmouseover="this.style.borderColor='#8A8378';this.style.color='#3D453F'" onmouseout="this.style.borderColor='#E0D8CC';this.style.color='#8A8378'">Skip</button>
                                <span class="fp-skip-tag" style="display:none; font-family:'IBM Plex Mono',monospace; font-size:10.5px; letter-spacing:1.4px; color:#8A8378; margin-left:12px;">SKIPPED</span>
                                <input type="hidden" name="answers[{{ $questionIndex }}][placement_question_id]" value="{{ $q->id }}">
                            </div>
                            @php $questionIndex++; @endphp
                        @endforeach

                    @else
                        {{-- MCQ option pills for grammar/vocabulary/reading --}}
                        @foreach ($partQuestions as $q)
                            @php
                                $options = [
                                    'a' => $q->option_a,
                                    'b' => $q->option_b,
                                    'c' => $q->option_c,
                                    'd' => $q->option_d,
                                ];
                            @endphp
                            <div data-q="{{ $questionIndex }}" style="margin-bottom: 16px;">
                                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; font-weight: 700; letter-spacing: -.3px; line-height: 1.35; margin-bottom: 12px; text-wrap: pretty;">{{ $q->question }}</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                    @foreach ($options as $letter => $option)
                                        @php
                                            $optionId = 'ans-' . $q->id . '-' . $letter;
                                            $checked = old('answers.' . $questionIndex . '.answer') === $letter;
                                        @endphp
                                        <label for="{{ $optionId }}" style="display: flex; align-items: center; gap: 9px; border: 1px solid #E0D8CC; border-radius: 999px; padding: 9px 16px; background: #FFFEFC; cursor: pointer; font-size: 13.5px; color: #3D453F; transition: border-color .2s, background .2s;" onmouseover="this.style.borderColor='#0E6B5C'" onmouseout="this.style.borderColor='#E0D8CC'">
                                            <input type="radio" id="{{ $optionId }}" name="answers[{{ $questionIndex }}][answer]" value="{{ $letter }}" @checked($checked) style="accent-color: #0E6B5C; margin: 0; cursor: pointer;">
                                            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #8A8378; letter-spacing: .6px; text-transform: uppercase;">{{ $letter }}</span>
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <button type="button" data-skip="{{ $questionIndex }}" style="border:1px solid #E0D8CC; background:#FFFEFC; border-radius:999px; padding:7px 16px; font:inherit; font-size:12.5px; color:#8A8378; cursor:pointer; transition:border-color .2s, color .2s; margin-top:12px;" onmouseover="this.style.borderColor='#8A8378';this.style.color='#3D453F'" onmouseout="this.style.borderColor='#E0D8CC';this.style.color='#8A8378'">Skip</button>
                                <span class="fp-skip-tag" style="display:none; font-family:'IBM Plex Mono',monospace; font-size:10.5px; letter-spacing:1.4px; color:#8A8378; margin-left:12px;">SKIPPED</span>
                                <input type="hidden" name="answers[{{ $questionIndex }}][placement_question_id]" value="{{ $q->id }}">
                            </div>
                            @php $questionIndex++; @endphp
                        @endforeach
                    @endif
                </div>
            @endforeach

            {{-- Live progress + submit --}}
            <div style="text-align: center; padding: 10px 0 20px;">
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #8A8378; letter-spacing: .3px; margin-bottom: 14px;">
                    Answered <span id="fp-answered-count" style="color: #0E6B5C; font-weight: 700;">0</span> of <span id="fp-total-count">0</span> — you can skip questions you don't know.
                </div>
                <button type="submit" class="fp-btn-dark" style="border-radius: 999px; padding: 14px 36px; font-size: 14px;">Submit placement test</button>
            </div>
        </form>

        <script>
        (function () {
            var form = document.getElementById('placement-form');
            if (!form) return;

            var answeredEl = document.getElementById('fp-answered-count');
            var totalEl = document.getElementById('fp-total-count');
            var containers = Array.prototype.slice.call(form.querySelectorAll('[data-q]'));
            var total = containers.length;

            if (totalEl) totalEl.textContent = total;

            function isAnswered(container) {
                return !!container.querySelector('input[type="radio"]:checked')
                    || (function () {
                        var ta = container.querySelector('textarea');
                        return !!ta && ta.value.trim() !== '';
                    })();
            }

            function countAnswered() {
                return containers.filter(isAnswered).length;
            }

            function unskip(container) {
                if (!container) return;
                container.style.opacity = '1';
                var tag = container.querySelector('.fp-skip-tag');
                if (tag) tag.style.display = 'none';
            }

            function refresh() {
                if (answeredEl) answeredEl.textContent = countAnswered();
            }

            form.addEventListener('change', refresh);
            form.addEventListener('input', refresh);

            containers.forEach(function (container) {
                container.querySelectorAll('input[type="radio"], textarea').forEach(function (el) {
                    el.addEventListener('change', function () { unskip(container); refresh(); });
                    el.addEventListener('input', function () { unskip(container); refresh(); });
                });
            });

            form.querySelectorAll('[data-skip]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var container = form.querySelector('[data-q="' + btn.getAttribute('data-skip') + '"]');
                    if (!container) return;
                    container.querySelectorAll('input[type="radio"]').forEach(function (radio) { radio.checked = false; });
                    var ta = container.querySelector('textarea');
                    if (ta) ta.value = '';
                    container.style.opacity = '0.55';
                    var tag = container.querySelector('.fp-skip-tag');
                    if (tag) tag.style.display = 'inline-block';
                    refresh();
                });
            });

            form.addEventListener('submit', function (event) {
                var unanswered = total - countAnswered();
                if (unanswered > 0) {
                    var message = 'You have ' + unanswered + ' unanswered question' + (unanswered === 1 ? '' : 's') + '. Submit anyway? Skipped and unanswered questions count as incorrect.';
                    if (!window.confirm(message)) event.preventDefault();
                }
            });

            refresh();
        })();
        </script>

    @endif
</div>
@endsection
