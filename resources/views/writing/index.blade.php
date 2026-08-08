@extends('layouts.app')

@section('title', 'Writing Practice')

@section('crumb', 'Practice')

@section('content')
@php
    use App\Enums\WritingSubmissionStatus;

    $latest = $submissions->first();
    $state = 'idle';
    if ($latest) {
        $state = match ($latest->status) {
            WritingSubmissionStatus::Corrected => 'done',
            WritingSubmissionStatus::Failed => 'failed',
            default => 'loading',
        };
    }
    $skeletonWidths = ['92%', '78%', '85%', '60%', '70%'];
@endphp

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 22px; align-items: start; animation: fadein .4s both;">
    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .5s both;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Writing task</div>
            <a href="/writing/submissions" style="font-size: 12.5px; color: #0E6B5C; text-decoration: none;" onmouseover="this.style.color='#E0603B'" onmouseout="this.style.color='#0E6B5C'">My submissions →</a>
        </div>

        <form method="POST" action="/writing" id="writing-form">
            @csrf

            <textarea name="prompt" rows="2" placeholder="Describe a day that changed your routine." style="width: 100%; margin-top: 18px; border: 1px solid #E0D8CC; border-radius: 14px; padding: 14px 16px; font: inherit; font-size: 14.5px; line-height: 1.6; color: #17211E; background: #FFFEFC; outline: none; resize: vertical; transition: border-color .2s, box-shadow .2s;" onfocus="this.style.borderColor='#0E6B5C';this.style.boxShadow='0 0 0 4px rgba(14,107,92,.1)'" onblur="this.style.borderColor='#E0D8CC';this.style.boxShadow='none'">{{ old('prompt') }}</textarea>

            <textarea name="original_text" rows="11" placeholder="Write 80–120 words…" required style="width: 100%; min-height: 250px; margin-top: 14px; border: 1px solid #E0D8CC; border-radius: 14px; padding: 16px; font: inherit; font-size: 14.5px; line-height: 1.7; color: #17211E; background: #FFFEFC; outline: none; resize: vertical; transition: border-color .2s, box-shadow .2s;" onfocus="this.style.borderColor='#0E6B5C';this.style.boxShadow='0 0 0 4px rgba(14,107,92,.1)'" onblur="this.style.borderColor='#E0D8CC';this.style.boxShadow='none'">{{ old('original_text') }}</textarea>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px;">
                <span style="font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: #8A8378;"><span id="word-count">0</span> words</span>
                <button type="submit" style="border: 0; border-radius: 999px; padding: 12px 24px; background: #E0603B; color: #FFF6F2; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 26px rgba(224,96,59,.3)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">Send for AI correction</button>
            </div>
        </form>
    </div>

    <div style="min-height: 300px;">
        @if ($state === 'idle')
            <div style="border: 1px dashed #CFC6B8; border-radius: 20px; padding: 40px; text-align: center; color: #8A8378; font-size: 13.5px; line-height: 1.6;">
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; color: #55605A; margin-bottom: 8px;">Feedback appears here</div>
                Corrections run asynchronously. You can leave the page — a notification arrives when the job finishes.
            </div>
        @elseif ($state === 'loading')
            <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: fadein .3s both;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 22px;">
                    <span style="width: 9px; height: 9px; border-radius: 50%; background: #E0603B; animation: pulse 1.4s infinite;"></span>
                    <span style="font-size: 13px; color: #55605A;">Correcting your text — grammar, vocabulary and fluency…</span>
                </div>
                @foreach ($skeletonWidths as $w)
                    <div style="height: 13px; border-radius: 7px; margin-bottom: 12px; width: {{ $w }}; background: linear-gradient(90deg, #EFE9DF 25%, #F8F4ED 37%, #EFE9DF 63%); background-size: 600px 100%; animation: shimmer 1.3s linear infinite;"></div>
                @endforeach
            </div>
            <script>
                (function () {
                    // Auto-refresh until the correction finishes (this block only renders while loading).
                    setTimeout(function () { window.location.reload(); }, 5000);
                })();
            </script>
        @elseif ($state === 'failed')
            <div style="background: #FFF6F2; border: 1px solid #F0C9B9; border-radius: 20px; padding: 30px; animation: rise .4s both;">
                <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #A73E1E;">AI PROVIDER UNAVAILABLE</div>
                <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 21px; font-weight: 700; margin: 10px 0 8px;">Correction could not be completed</div>
                <div style="font-size: 13.5px; color: #7A5A4C; line-height: 1.6;">Your text is saved and the submission is marked as <strong>failed</strong>. The failure is logged; retrying submits the same text for correction again.</div>
                <form method="POST" action="/writing">
                    @csrf
                    <input type="hidden" name="prompt" value="{{ $latest->prompt }}">
                    <input type="hidden" name="original_text" value="{{ $latest->original_text }}">
                    <button type="submit" style="margin-top: 20px; border: 0; border-radius: 999px; padding: 12px 22px; background: #E0603B; color: #FFF6F2; font: inherit; font-size: 13px; font-weight: 600; cursor: pointer;">Retry correction</button>
                </form>
            </div>
        @else
            @php
                $score = (int) round((float) $latest->score);
                $mistakeCount = is_array($latest->mistakes) ? count($latest->mistakes) : 0;
            @endphp
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div style="background: #17211E; color: #EFEAE2; border-radius: 20px; padding: 24px; display: flex; align-items: center; gap: 22px; animation: rise .5s both;">
                    <div style="flex: none;">
                        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 46px; font-weight: 800; line-height: 1; color: #29C39F;">{{ $score }}</div>
                        <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #7E9089; margin-top: 4px;">Score</div>
                    </div>
                    <div style="font-size: 13.5px; line-height: 1.6; color: #C6D1CC;">
                        {{ $mistakeCount > 0 ? 'The AI corrected '.$mistakeCount.' '.Str::plural('mistake', $mistakeCount).' across your text.' : 'No mistakes detected — clean writing. Keep it up!' }}
                    </div>
                </div>

                @forelse ((array) $latest->mistakes as $i => $m)
                    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px 20px; animation: rise .5s {{ 0.1 + $i * 0.06 }}s both;">
                        @if (is_array($m) && isset($m['correction']))
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 15px; flex-wrap: wrap;">
                                <span style="text-decoration: line-through; color: #C0553A;">{{ $m['original'] }}</span>
                                <span style="color: #A09889;">→</span>
                                <span style="font-weight: 600; color: #0A5347;">{{ $m['correction'] }}</span>
                            </div>
                            <div style="font-size: 12.5px; color: #6C6A63; margin-top: 7px;">{{ $m['rule'] }}</div>
                        @elseif (is_array($m) && isset($m['original']))
                            <div style="display: flex; align-items: center; gap: 10px; font-size: 15px; flex-wrap: wrap;">
                                <span style="text-decoration: line-through; color: #C0553A;">{{ $m['original'] }}</span>
                            </div>
                            <div style="font-size: 12.5px; color: #6C6A63; margin-top: 7px;">{{ $m['rule'] ?? '' }}</div>
                        @else
                            <div style="font-size: 13.5px; color: #3D453F;">{{ $m }}</div>
                        @endif
                    </div>
                @empty
                    <div style="background: #E3F2EE; border-radius: 16px; padding: 18px 20px; animation: rise .5s .1s both; font-size: 13.5px; color: #0A5347;">No mistakes found — great control of grammar and spelling.</div>
                @endforelse

                @if ($latest->grammar_feedback)
                    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px 20px; animation: rise .5s .24s both;">
                        <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #0E6B5C;">Grammar</div>
                        <div style="font-size: 13.5px; line-height: 1.6; color: #3D453F; margin-top: 7px;">{{ $latest->grammar_feedback }}</div>
                    </div>
                @endif
                @if ($latest->vocabulary_feedback)
                    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px 20px; animation: rise .5s .3s both;">
                        <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #0E6B5C;">Vocabulary</div>
                        <div style="font-size: 13.5px; line-height: 1.6; color: #3D453F; margin-top: 7px;">{{ $latest->vocabulary_feedback }}</div>
                    </div>
                @endif
                @if ($latest->fluency_feedback)
                    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 16px; padding: 18px 20px; animation: rise .5s .36s both;">
                        <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #0E6B5C;">Fluency</div>
                        <div style="font-size: 13.5px; line-height: 1.6; color: #3D453F; margin-top: 7px;">{{ $latest->fluency_feedback }}</div>
                    </div>
                @endif

                @if (is_array($latest->next_topics) && count($latest->next_topics) > 0)
                    <div style="background: #E3F2EE; border-radius: 16px; padding: 18px 20px; animation: rise .5s .42s both;">
                        <div style="font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #0A5347;">Next topics queued</div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px;">
                            @foreach ($latest->next_topics as $topic)
                                <span style="background: #FFFDFA; border-radius: 999px; padding: 7px 14px; font-size: 12.5px; color: #0A5347;">{{ $topic }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div style="display: flex; gap: 10px; animation: rise .5s .5s both;">
                    <a href="/writing" style="border: 1px solid #17211E; border-radius: 999px; padding: 12px 22px; background: none; font: inherit; font-size: 13px; font-weight: 500; color: #17211E; text-decoration: none; transition: all .2s;" onmouseover="this.style.background='#17211E';this.style.color='#EFEAE2'" onmouseout="this.style.background='none';this.style.color='#17211E'">Practice again</a>
                    <a href="/writing/submissions" style="border: 1px solid #E0D8CC; border-radius: 999px; padding: 12px 22px; background: none; font: inherit; font-size: 13px; color: #8A8378; text-decoration: none; transition: border-color .2s;" onmouseover="this.style.borderColor='#17211E'" onmouseout="this.style.borderColor='#E0D8CC'">My submissions →</a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('writing-form').querySelector('textarea[name="original_text"]').addEventListener('input', function () {
    const words = this.value.trim().split(/\s+/).filter(w => w.length > 0).length;
    document.getElementById('word-count').textContent = words;
});
</script>
@endsection
