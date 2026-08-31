@extends('layouts.app')

@section('title', 'My Roadmap')
@section('crumb', '4 weeks, regenerated as you learn')

@section('content')
<div style="animation: fadein .4s both;">

    @if ($processing)
        {{-- Processing skeleton --}}
        <div class="fp-grid-4" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;">
            @foreach (range(1, 4) as $i)
                <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px; animation: rise .6s {{ ($i - 1) * 0.09 }}s both cubic-bezier(.2,.9,.2,1);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div class="fp-skeleton" style="width: 60px; height: 12px;"></div>
                        <div class="fp-skeleton" style="width: 48px; height: 18px; border-radius: 999px;"></div>
                    </div>
                    <div class="fp-skeleton" style="width: 80%; height: 16px;"></div>
                    <div style="display: flex; flex-direction: column; gap: 9px;">
                        @foreach (range(1, 4) as $j)
                            <div class="fp-skeleton" style="width: {{ 70 + ($j * 5) }}%; height: 13px;"></div>
                        @endforeach
                    </div>
                    <div style="margin-top: auto; height: 6px; border-radius: 999px; background: #ECE5DA; overflow: hidden;">
                        <div class="fp-skeleton" style="width: 100%; height: 100%; border-radius: 999px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top: 16px; text-align: center; font-size: 13px; color: #8A8378;">Generating your roadmap...</div>
        <script>
            (function () {
                // Auto-refresh until generation finishes (this block only renders while processing).
                setTimeout(function () { window.location.reload(); }, 5000);
            })();
        </script>

    @elseif ($failed)
        {{-- Failed state --}}
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 50px 40px; text-align: center; animation: rise .5s both;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 22px; font-weight: 700; letter-spacing: -.4px;">Roadmap generation failed</div>
            <div style="font-size: 14px; color: #6C6A63; margin-top: 8px; line-height: 1.6; max-width: 420px; margin-left: auto; margin-right: auto;">The AI couldn't produce a valid 4-week plan this time — every roadmap must contain exactly 16 lessons from the catalog, 4 per week. Try again and a fresh generation will run.</div>
            <form method="POST" action="/roadmap" style="margin-top: 22px;">
                @csrf
                <button type="submit" style="border: 0; border-radius: 999px; padding: 13px 26px; background: #0E6B5C; color: #F2FBF8; font: inherit; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">Try again</button>
            </form>
        </div>

    @elseif ($roadmap === null)
        {{-- Empty state --}}
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 50px 40px; text-align: center; animation: rise .5s both;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 22px; font-weight: 700; letter-spacing: -.4px;">No roadmap yet</div>
            <div style="font-size: 14px; color: #6C6A63; margin-top: 8px; line-height: 1.6; max-width: 380px; margin-left: auto; margin-right: auto;">Take the placement test to generate your personalized 4-week learning roadmap.</div>
            <a href="/placement-test" style="display: inline-block; margin-top: 22px; border: 0; border-radius: 999px; padding: 13px 26px; background: #0E6B5C; color: #F2FBF8; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: transform .2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">Take the placement test →</a>
        </div>

    @else
        {{-- Week cards --}}
        @php
            $weeks = $roadmap->roadmapWeeks->sortBy('week_number');
            $totalWeeks = $weeks->count();
            if ($totalWeeks === 0) $totalWeeks = 4;

            // Determine current week (first not fully completed)
            $currentWeekNum = null;
            foreach ($weeks as $week) {
                $weekLessonIds = $week->roadmapWeekLessons->pluck('lesson_id')->toArray();
                $weekCompleted = count(array_intersect($weekLessonIds, $completedLessonIds));
                if ($weekCompleted < count($weekLessonIds)) {
                    $currentWeekNum = $week->week_number;
                    break;
                }
            }
            // If all done, all are Done
            if ($currentWeekNum === null && $weeks->isNotEmpty()) {
                $currentWeekNum = $weeks->max('week_number') + 1; // all done
            }
        @endphp

        <div class="fp-grid-4" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;">
            @foreach ($weeks->take(4) as $week)
                @php
                    $weekLessonIds = $week->roadmapWeekLessons->pluck('lesson_id')->toArray();
                    $weekCompleted = count(array_intersect($weekLessonIds, $completedLessonIds));
                    $weekTotal = count($weekLessonIds);
                    $weekPct = $weekTotal > 0 ? round(($weekCompleted / $weekTotal) * 100) : 0;

                    // Determine status
                    if ($weekPct >= 100) {
                        $status = 'Done';
                    } elseif ($week->week_number === $currentWeekNum) {
                        $status = 'Active';
                    } else {
                        $status = $week->week_number > ($currentWeekNum ?? 0) ? 'Locked' : 'Done';
                    }

                    // Colors per status
                    $isActive = $status === 'Active';
                    $isDone = $status === 'Done';
                    $isLocked = $status === 'Locked';

                    $bg = $isActive ? '#17211E' : '#FFFDFA';
                    $fg = $isActive ? '#EFEAE2' : ($isLocked ? '#9A9284' : '#17211E');
                    $border = $isActive ? '#17211E' : '#E5DDD2';
                    $tagBg = $isActive ? '#29C39F' : ($isLocked ? '#F1ECE3' : '#E3F2EE');
                    $tagFg = $isActive ? '#06231D' : ($isLocked ? '#9A9284' : '#0A5347');
                    $trackBg = $isActive ? '#2C3A35' : '#ECE5DA';
                    $barBg = $isActive ? '#29C39F' : ($isLocked ? '#E0D8CC' : '#0E6B5C');

                    // Map lesson skills to display kinds
                    $kindMap = [
                        'grammar' => 'GRAMMAR',
                        'vocabulary' => 'VOCAB',
                        'reading' => 'READING',
                        'writing' => 'WRITE',
                    ];
                @endphp

                <div style="background: {{ $bg }}; color: {{ $fg }}; border: 1px solid {{ $border }}; border-radius: 20px; padding: 24px; display: flex; flex-direction: column; gap: 16px; animation: rise .6s {{ ($loop->index * 0.09) }}s both cubic-bezier(.2,.9,.2,1); transition: transform .28s, box-shadow .28s;" onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 20px 40px rgba(23,33,30,.12)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; opacity: .7;">Week {{ $week->week_number }}</div>
                        <div style="font-size: 10.5px; letter-spacing: 1.3px; text-transform: uppercase; padding: 4px 9px; border-radius: 999px; background: {{ $tagBg }}; color: {{ $tagFg }};">{{ $status }}</div>
                    </div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 19px; font-weight: 700; letter-spacing: -.4px; line-height: 1.25;">{{ $week->objective }}</div>
                    <div style="display: flex; flex-direction: column; gap: 9px;">
                        @foreach ($week->roadmapWeekLessons->sortBy('display_order') as $rwl)
                            @php
                                $lesson = $rwl->lesson;
                                $kind = $lesson ? ($kindMap[$lesson->skill->value] ?? strtoupper($lesson->skill->value)) : 'LESSON';
                                $lessonTitle = $lesson ? $lesson->title : 'Lesson';
                                $isCompleted = in_array($lesson?->id, $completedLessonIds);
                            @endphp
                            <div style="display: flex; gap: 9px; font-size: 13px; line-height: 1.45;">
                                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; opacity: .55; padding-top: 2px;">{{ $kind }}</span>
                                @if ($lesson && !$isLocked)
                                    <a href="/lessons/{{ $lesson->id }}" style="color: inherit; text-decoration: none; {{ $isCompleted ? 'text-decoration: line-through; opacity: .7;' : '' }}">{{ $lessonTitle }}</a>
                                @else
                                    <span>{{ $lessonTitle }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top: auto; height: 6px; border-radius: 999px; background: {{ $trackBg }}; overflow: hidden;">
                        <div style="height: 100%; width: {{ $weekPct }}%; background: {{ $barBg }}; border-radius: 999px; animation: slideL 1s {{ $loop->index * 0.09 }}s both;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 20px; background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 18px; padding: 22px 26px; font-size: 13.5px; color: #55605A; display: flex; gap: 14px; align-items: center; animation: rise .6s .4s both;">
            <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #0E6B5C;">BR04</span>
            <span>Every lesson in this plan already exists in the catalog. The AI orders and schedules content — it never invents it.</span>
        </div>
    @endif
</div>
@endsection
