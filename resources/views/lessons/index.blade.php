@extends('layouts.app')

@section('title', 'Lessons')
@section('crumb', 'Lessons & exercises')

@section('content')
<div style="animation: fadein .4s both;">

    @if ($level === null)
        <div style="background: #FFFDFA; border: 1px dashed #CFC6B8; border-radius: 20px; padding: 64px 40px; text-align: center; animation: rise .5s both;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: -.4px; color: #17211E;">Complete your placement test first</div>
            <div style="font-size: 14px; color: #6C6A63; margin-top: 12px; max-width: 440px; margin-left: auto; margin-right: auto; line-height: 1.7;">Your lessons are personalised to your level. Take the placement test so we can show you the lessons and exercises that match your English level.</div>
            <a href="/placement-test" style="display: inline-block; margin-top: 26px; border: 0; border-radius: 999px; padding: 14px 28px; background: #17211E; color: #EFEAE2; font: inherit; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">Take the placement test →</a>
        </div>
    @else

    {{-- Filter pills --}}
    <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; align-items: center;" id="filter-row">
        <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; letter-spacing: .6px; padding: 8px 14px; border-radius: 999px; background: #17211E; color: #29C39F;">{{ $level->value }} level</span>
        @foreach (['All', 'Grammar', 'Vocabulary', 'Reading', 'Writing'] as $filter)
            <button
                type="button"
                class="fp-filter-pill"
                data-filter="{{ $filter }}"
                onclick="setFilter('{{ $filter }}')"
                style="border-radius: 999px; padding: 9px 17px; font: inherit; font-size: 12.5px; cursor: pointer; transition: all .22s; border: 1px solid {{ $filter === 'All' ? '#17211E' : '#E5DDD2' }}; background: {{ $filter === 'All' ? '#17211E' : '#FFFDFA' }}; color: {{ $filter === 'All' ? '#EFEAE2' : '#55605A' }};"
                onmouseover="this.style.transform='translateY(-2px)'"
                onmouseout="this.style.transform='none'"
            >{{ $filter }}</button>
        @endforeach
    </div>

    {{-- Lesson cards grid --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;" id="lessons-grid">
        @forelse ($lessons as $lesson)
            @php
                $isCompleted = in_array($lesson->id, $completedLessonIds);
                $skill = $lesson->skill->value;
                $skillDisplay = ucfirst($skill);
                $levelDisplay = $lesson->level->value;
                $delay = $loop->index * 0.06;

                // Derive a friendly description from skill/level
                $descriptions = [
                    'grammar' => 'Grammar rules, tenses, and sentence structure for ' . $levelDisplay . ' level.',
                    'vocabulary' => 'Word building, collocations, and everyday expressions.',
                    'reading' => 'Comprehension, skimming, and inference practice.',
                    'writing' => 'Paragraph structure, coherence, and written expression.',
                ];

                // Prefer a real excerpt from the lesson content: the first plain
                // paragraph after a heading, truncated to two lines.
                $desc = '';
                if ($lesson->content) {
                    foreach (preg_split('/\R/', trim($lesson->content)) ?: [] as $line) {
                        $line = trim($line);
                        if ($line === '' || str_starts_with($line, '## ')) {
                            continue;
                        }
                        if (str_starts_with($line, '- ') || str_starts_with($line, '> ')) {
                            continue;
                        }
                        $desc = $line;
                        break;
                    }
                    if ($desc !== '' && mb_strlen($desc) > 150) {
                        $desc = mb_substr($desc, 0, 147).'…';
                    }
                }
                if ($desc === '') {
                    $desc = $descriptions[$skill] ?? 'A focused lesson to build your skills.';
                }
            @endphp

            <div
                class="lesson-card"
                data-skill="{{ $skillDisplay }}"
                style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 18px; padding: 22px; display: flex; flex-direction: column; gap: 12px; animation: rise .55s {{ $delay }}s both cubic-bezier(.2,.9,.2,1); transition: transform .26s, box-shadow .26s;"
                onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 18px 38px rgba(23,33,30,.1)'"
                onmouseout="this.style.transform='none';this.style.boxShadow='none'"
            >
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 10.5px; letter-spacing: 1.4px; text-transform: uppercase; color: #8A8378;">{{ $skillDisplay }}</span>
                    <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; padding: 3px 8px; border-radius: 6px; background: #F1ECE3; color: #55605A;">{{ $levelDisplay }}</span>
                </div>
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17.5px; font-weight: 700; letter-spacing: -.3px; line-height: 1.28;">{{ $lesson->title }}</div>
                <div style="font-size: 13px; color: #6C6A63; line-height: 1.55;">{{ $desc }}</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 6px;">
                    <span style="font-size: 12px; color: {{ $isCompleted ? '#0E6B5C' : '#8A8378' }}; font-weight: 500;">{{ $isCompleted ? 'Completed' : 'Not started' }}</span>
                    <a href="/lessons/{{ $lesson->id }}" style="border: 1px solid #17211E; border-radius: 999px; padding: 8px 15px; font-size: 12px; font-weight: 500; background: {{ $isCompleted ? '#F1ECE3' : '#FFFDFA' }}; color: #17211E; text-decoration: none; transition: all .2s;" onmouseover="this.style.background='#17211E';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ $isCompleted ? '#F1ECE3' : '#FFFDFA' }}';this.style.color='#17211E'">{{ $isCompleted ? 'Review' : 'Open lesson' }}</a>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px 40px;">
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 20px; font-weight: 700;">No lessons available</div>
                <div style="font-size: 14px; color: #6C6A63; margin-top: 8px;">Lessons will appear once they are published to the catalog.</div>
            </div>
        @endforelse
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
    function setFilter(filter) {
        // Update pill styles
        document.querySelectorAll('.fp-filter-pill').forEach(function (btn) {
            if (btn.dataset.filter === filter) {
                btn.style.background = '#17211E';
                btn.style.color = '#EFEAE2';
                btn.style.borderColor = '#17211E';
            } else {
                btn.style.background = '#FFFDFA';
                btn.style.color = '#55605A';
                btn.style.borderColor = '#E5DDD2';
            }
        });

        // Show/hide cards
        document.querySelectorAll('.lesson-card').forEach(function (card) {
            if (filter === 'All' || card.dataset.skill === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endsection
