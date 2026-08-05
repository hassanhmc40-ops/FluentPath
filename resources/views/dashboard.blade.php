@extends('layouts.app')

@section('title', 'Dashboard')
@section('crumb', 'Adaptive learning cycle')

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px; animation: fadein .4s both;">

    @if ($data === null)
        {{-- Admin placeholder --}}
        <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 22px; padding: 50px 40px; text-align: center; animation: rise .5s both;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 22px; font-weight: 700; letter-spacing: -.4px;">Admin Console</div>
            <div style="font-size: 14px; color: #6C6A63; margin-top: 8px; line-height: 1.6;">Use the sidebar to navigate to Lessons, Exercises, Students, or Overview.</div>
            <a href="/admin" style="display: inline-block; margin-top: 20px; border: 0; border-radius: 999px; padding: 12px 24px; background: #17211E; color: #EFEAE2; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">Go to Overview</a>
        </div>

    @else
        {{-- Student dashboard --}}
        @php
            $level = $data['cefr_level'] ? $data['cefr_level']->value : null;
            $weekNum = $data['current_week'] ?? 0;
            $lessonsCompleted = $data['lessons']['completed'] ?? 0;
            $lessonsTotal = $data['lessons']['total'] ?? 0;
            $writingHistory = $data['writing_score_history'] ?? [];
            $grammarTrend = $data['grammar_improvement'] ?? [];
            $vocabTrend = $data['vocabulary_improvement'] ?? [];
            $streak = $data['learning_streak'] ?? 0;
            $progressPct = $data['overall_progress_percentage'] ?? 0;
            $nextAction = $data['next_recommended_action'] ?? null;

            $grammarScore = $grammarTrend['current_score'] ?? null;
            $vocabScore = $vocabTrend['current_score'] ?? null;

            // Compute writing stats
            $avgWriting = 0;
            $writingScores = [];
            foreach ($writingHistory as $wh) {
                $score = (float) ($wh->score ?? 0);
                $writingScores[] = $score;
            }
            if (count($writingScores) > 0) {
                $avgWriting = round(array_sum($writingScores) / count($writingScores));
            }

            // Last 8 bars
            $barScores = array_slice($writingScores, -8);
        @endphp

        {{-- Hero card --}}
        <div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 20px;">
            <div style="background: #17211E; color: #EFEAE2; border-radius: 22px; padding: 30px; position: relative; overflow: hidden; animation: rise .55s both cubic-bezier(.2,.9,.2,1);">
                <div style="position: absolute; right: -70px; top: -70px; width: 240px; height: 240px; border-radius: 50%; background: radial-gradient(circle, rgba(41,195,159,.35), transparent 70%); animation: drift 16s ease-in-out infinite;"></div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; position: relative;">
                    <div>
                        <div style="font-size: 10.5px; letter-spacing: 1.7px; text-transform: uppercase; color: #7E9089;">Current level</div>
                        @if ($level)
                            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 72px; font-weight: 800; line-height: .95; letter-spacing: -3px; margin: 8px 0 6px;">{{ $level }}</div>
                            <div style="font-size: 13.5px; color: #A6B4AE; max-width: 300px; line-height: 1.55;">
                                @if ($weekNum)
                                    Continue week {{ $weekNum }}.
                                @else
                                    Your level has been set. Start your roadmap.
                                @endif
                            </div>
                            @if ($nextAction && $nextAction['lesson_id'])
                                <a href="/roadmap" style="display: inline-block; margin-top: 20px; border: 0; border-radius: 999px; padding: 12px 22px; background: #29C39F; color: #06231D; font-size: 13px; font-weight: 600; text-decoration: none; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Continue week {{ $weekNum }} →</a>
                            @else
                                <a href="/roadmap" style="display: inline-block; margin-top: 20px; border: 0; border-radius: 999px; padding: 12px 22px; background: #29C39F; color: #06231D; font-size: 13px; font-weight: 600; text-decoration: none; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">View roadmap →</a>
                            @endif
                        @else
                            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 72px; font-weight: 800; line-height: .95; letter-spacing: -3px; margin: 8px 0 6px; color: #7E9089;">—</div>
                            <div style="font-size: 13.5px; color: #A6B4AE; max-width: 300px; line-height: 1.55;">No level set yet. Take the placement test to get started.</div>
                            <a href="/placement-test" style="display: inline-block; margin-top: 20px; border: 0; border-radius: 999px; padding: 12px 22px; background: #29C39F; color: #06231D; font-size: 13px; font-weight: 600; text-decoration: none; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(41,195,159,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Take the placement test →</a>
                        @endif
                    </div>
                    @if ($level && $progressPct > 0)
                        <div style="position: relative; width: 132px; height: 132px; flex: none;">
                            <svg viewBox="0 0 120 120" style="width: 132px; height: 132px; transform: rotate(-90deg);">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="#2C3A35" stroke-width="11"></circle>
                                <circle cx="60" cy="60" r="52" fill="none" stroke="#29C39F" stroke-width="11" stroke-linecap="round" stroke-dasharray="327" stroke-dashoffset="{{ round(327 - (327 * $progressPct / 100)) }}" style="animation: dash 1.5s .3s both cubic-bezier(.2,.9,.2,1);"></circle>
                            </svg>
                            <div style="position: absolute; inset: 0; display: grid; place-items: center; text-align: center;">
                                <div>
                                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 27px; font-weight: 700;">{{ round($progressPct) }}%</div>
                                    <div style="font-size: 10px; letter-spacing: 1.3px; text-transform: uppercase; color: #7E9089;">Week {{ $weekNum }} of 4</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 19px; animation: rise .55s .05s both cubic-bezier(.2,.9,.2,1); transition: transform .25s, box-shadow .25s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 34px rgba(23,33,30,.09)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Lessons done</div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 32px; font-weight: 700; letter-spacing: -1.2px; margin: 7px 0 4px; color: #17211E;">{{ $lessonsCompleted }}/{{ $lessonsTotal }}</div>
                    <div style="font-size: 12px; color: #8A8378;">of {{ $lessonsTotal }} total</div>
                </div>
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 19px; animation: rise .55s .1s both cubic-bezier(.2,.9,.2,1); transition: transform .25s, box-shadow .25s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 34px rgba(23,33,30,.09)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Avg writing</div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 32px; font-weight: 700; letter-spacing: -1.2px; margin: 7px 0 4px; color: #0E6B5C;">{{ $avgWriting ?: '—' }}</div>
                    <div style="font-size: 12px; color: #8A8378;">{{ count($writingScores) }} {{ Str::plural('submission', count($writingScores)) }}</div>
                </div>
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 19px; animation: rise .55s .15s both cubic-bezier(.2,.9,.2,1); transition: transform .25s, box-shadow .25s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 34px rgba(23,33,30,.09)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Exercises</div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 32px; font-weight: 700; letter-spacing: -1.2px; margin: 7px 0 4px; color: #17211E;">{{ $lessonsCompleted }}</div>
                    <div style="font-size: 12px; color: #8A8378;">completed</div>
                </div>
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 19px; animation: rise .55s .2s both cubic-bezier(.2,.9,.2,1); transition: transform .25s, box-shadow .25s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 34px rgba(23,33,30,.09)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Grammar gain</div>
                    @if ($grammarScore !== null && isset($grammarTrend['start_score']) && $grammarTrend['start_score'] !== null)
                        @php $gain = $grammarScore - $grammarTrend['start_score']; @endphp
                        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 32px; font-weight: 700; letter-spacing: -1.2px; margin: 7px 0 4px; color: {{ $gain >= 0 ? '#E0603B' : '#A73E1E' }};">{{ $gain >= 0 ? '+' : '' }}{{ round($gain) }}</div>
                        <div style="font-size: 12px; color: #8A8378;">since placement</div>
                    @else
                        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 32px; font-weight: 700; letter-spacing: -1.2px; margin: 7px 0 4px; color: #B5AC9D;">—</div>
                        <div style="font-size: 12px; color: #8A8378;">take the placement test</div>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.15fr 1fr; gap: 20px;">
            {{-- Writing score history --}}
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .55s .2s both cubic-bezier(.2,.9,.2,1);">
                <div style="display: flex; align-items: baseline; justify-content: space-between;">
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700;">Writing score history</div>
                    <div style="font-size: 12px; color: #8A8378;">last {{ count($barScores) }} {{ Str::plural('submission', count($barScores)) }}</div>
                </div>
                <div style="display: flex; align-items: flex-end; gap: 10px; height: 150px; margin-top: 22px;">
                    @forelse ($barScores as $i => $score)
                        @php
                            $h = max(8, round($score * 1.35));
                            $isLast = $loop->last;
                        @endphp
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 8px; height: 100%;">
                            <div style="font-size: 11px; font-family: 'IBM Plex Mono', monospace; color: #6C6A63;">{{ $score }}</div>
                            <div style="width: 100%; border-radius: 8px 8px 3px 3px; background: {{ $isLast ? '#0E6B5C' : '#CDE7E0' }}; height: {{ $h }}px; transform-origin: bottom; animation: grow .7s {{ 0.15 + ($i * 0.07) }}s both cubic-bezier(.2,.9,.2,1);"></div>
                            <div style="font-size: 10.5px; color: #A09889;">S{{ $i + 1 }}</div>
                        </div>
                    @empty
                        <div style="width: 100%; text-align: center; padding: 40px 0;">
                            <div style="font-size: 13.5px; color: #8A8378;">No writing submissions yet.</div>
                            <a href="/writing" style="display: inline-block; margin-top: 12px; border: 1px solid #17211E; border-radius: 999px; padding: 10px 18px; background: none; color: #17211E; font-size: 12.5px; font-weight: 500; text-decoration: none; transition: all .2s;" onmouseover="this.style.background='#17211E';this.style.color='#EFEAE2'" onmouseout="this.style.background='none';this.style.color='#17211E'">Start writing →</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Skill balance --}}
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .55s .28s both cubic-bezier(.2,.9,.2,1);">
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700;">Skill balance</div>
                <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 22px;">
                    @php
                        $skills = [
                            ['name' => 'Grammar', 'score' => $grammarScore, 'bg' => '#0E6B5C'],
                            ['name' => 'Vocabulary', 'score' => $vocabScore, 'bg' => '#29C39F'],
                            ['name' => 'Reading', 'score' => null, 'bg' => '#17211E'],
                            ['name' => 'Writing', 'score' => $avgWriting ?: null, 'bg' => '#E0603B'],
                        ];
                    @endphp
                    @foreach ($skills as $i => $skill)
                        @php $displayScore = $skill['score'] !== null ? round($skill['score']) : 0; @endphp
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 7px;">
                                <span style="font-weight: 500;">{{ $skill['name'] }}</span>
                                <span style="font-family: 'IBM Plex Mono', monospace; color: #6C6A63;">{{ $skill['score'] !== null ? round($skill['score']) : '—' }}</span>
                            </div>
                            <div style="height: 8px; border-radius: 999px; background: #ECE5DA; overflow: hidden;">
                                <div style="height: 100%; border-radius: 999px; width: {{ $displayScore }}%; background: {{ $skill['bg'] }}; animation: slideL .9s {{ 0.2 + ($i * 0.09) }}s both cubic-bezier(.2,.9,.2,1);"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Next up --}}
        @if ($nextAction && ($nextAction['lesson_id'] || $nextAction['writing_prompt']))
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 22px 26px; display: flex; align-items: center; gap: 18px; animation: rise .55s .35s both cubic-bezier(.2,.9,.2,1);">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #E3F2EE; display: grid; place-items: center; font-size: 16px; flex: none;">→</div>
                <div style="flex: 1;">
                    <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #0E6B5C;">Next up</div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 15px; font-weight: 600; margin-top: 3px;">
                        @if ($nextAction['lesson_title'])
                            {{ $nextAction['lesson_title'] }}
                        @elseif ($nextAction['topic'])
                            {{ $nextAction['topic'] }}
                        @elseif ($nextAction['writing_prompt'])
                            {{ Str::limit($nextAction['writing_prompt'], 60) }}
                        @endif
                    </div>
                </div>
                @if ($nextAction['lesson_id'])
                    <a href="/lessons/{{ $nextAction['lesson_id'] }}" style="border: 0; border-radius: 999px; padding: 10px 18px; background: #17211E; color: #EFEAE2; font-size: 12.5px; font-weight: 600; text-decoration: none; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">Open lesson →</a>
                @elseif ($nextAction['writing_prompt'])
                    <a href="/writing" style="border: 0; border-radius: 999px; padding: 10px 18px; background: #E0603B; color: #FFF6F2; font-size: 12.5px; font-weight: 600; text-decoration: none; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(224,96,59,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Start writing →</a>
                @endif
            </div>
        @endif

    @endif
</div>
@endsection
