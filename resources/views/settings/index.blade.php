@extends('layouts.app')

@section('title', 'Settings')

@section('crumb', 'Account')

@section('content')
@php
    $weeklyGoal = $user->weekly_hours ?? '—';
@endphp

<div style="max-width: 700px; display: flex; flex-direction: column; gap: 18px; animation: fadein .4s both;">
    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .5s both;">
        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; font-weight: 700;">Profile</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 18px;">
            <label style="display: block;">
                <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Full name</span>
                <input readonly value="{{ $user->name }}" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #F8F5F0; color: #55605A; outline: none; cursor: default;">
            </label>
            <label style="display: block;">
                <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Email</span>
                <input readonly value="{{ $user->email }}" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #F8F5F0; color: #55605A; outline: none; cursor: default;">
            </label>
            <label style="display: block;">
                <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Weekly goal</span>
                <input readonly value="{{ $weeklyGoal }}" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #F8F5F0; color: #55605A; outline: none; cursor: default;">
            </label>
            <label style="display: block;">
                <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Time zone</span>
                <input readonly value="Africa/Casablanca" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 11px; padding: 12px 14px; font-size: 14px; background: #F8F5F0; color: #55605A; outline: none; cursor: default;">
            </label>
        </div>
    </div>

    <div style="background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 20px; padding: 26px; animation: rise .5s .08s both;">
        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 18px; font-weight: 700;">Preferences</div>
        <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 10px;">
            <button type="button" onclick="fpFlipToggle(this)" data-on="1" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; border: 0; background: none; padding: 14px 0; border-bottom: 1px solid #F1ECE3; cursor: pointer; font: inherit; text-align: left; width: 100%;">
                <span>
                    <span style="display: block; font-size: 14px; font-weight: 500;">Inactivity reminders</span>
                    <span style="display: block; font-size: 12.5px; color: #8A8378; margin-top: 3px;">Daily scheduled task emails you after 3 quiet days.</span>
                </span>
                <span class="fp-track" style="width: 44px; height: 25px; border-radius: 999px; flex: none; padding: 3px; transition: background .25s; background: #0E6B5C;">
                    <span class="fp-knob" style="display: block; width: 19px; height: 19px; border-radius: 50%; background: #FFFDFA; transition: transform .25s cubic-bezier(.2,.9,.2,1); transform: translateX(19px);"></span>
                </span>
            </button>
            <button type="button" onclick="fpFlipToggle(this)" data-on="0" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; border: 0; background: none; padding: 14px 0; border-bottom: 1px solid #F1ECE3; cursor: pointer; font: inherit; text-align: left; width: 100%;">
                <span>
                    <span style="display: block; font-size: 14px; font-weight: 500;">Weekly progress digest</span>
                    <span style="display: block; font-size: 12.5px; color: #8A8378; margin-top: 3px;">A Monday summary of scores and next steps.</span>
                </span>
                <span class="fp-track" style="width: 44px; height: 25px; border-radius: 999px; flex: none; padding: 3px; transition: background .25s; background: #DED6C9;">
                    <span class="fp-knob" style="display: block; width: 19px; height: 19px; border-radius: 50%; background: #FFFDFA; transition: transform .25s cubic-bezier(.2,.9,.2,1); transform: translateX(0);"></span>
                </span>
            </button>
            <button type="button" onclick="fpFlipToggle(this)" data-on="0" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; border: 0; background: none; padding: 14px 0; border-bottom: 1px solid #F1ECE3; cursor: pointer; font: inherit; text-align: left; width: 100%;">
                <span>
                    <span style="display: block; font-size: 14px; font-weight: 500;">Sound on correction ready</span>
                    <span style="display: block; font-size: 12.5px; color: #8A8378; margin-top: 3px;">Short chime when an AI job finishes.</span>
                </span>
                <span class="fp-track" style="width: 44px; height: 25px; border-radius: 999px; flex: none; padding: 3px; transition: background .25s; background: #DED6C9;">
                    <span class="fp-knob" style="display: block; width: 19px; height: 19px; border-radius: 50%; background: #FFFDFA; transition: transform .25s cubic-bezier(.2,.9,.2,1); transform: translateX(0);"></span>
                </span>
            </button>
        </div>
    </div>

    @if (!$user->isAdmin())
    <div style="background: #FFF6F2; border: 1px solid #F0C9B9; border-radius: 20px; padding: 24px; animation: rise .5s .16s both;">
        <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 17px; font-weight: 700; color: #A73E1E;">Danger zone</div>
        <div style="font-size: 13px; color: #7A5A4C; margin-top: 6px; line-height: 1.55;">Retaking the placement test archives your current roadmap and regenerates a new 4-week plan.</div>
        <div style="display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap;">
            <form method="POST" action="/placement-test/retake">
                @csrf
                <button type="submit" style="border: 1px solid #E0603B; background: none; color: #A73E1E; border-radius: 999px; padding: 11px 20px; font: inherit; font-size: 12.5px; cursor: pointer; transition: all .2s;" onmouseover="this.style.background='#E0603B';this.style.color='#FFF6F2'" onmouseout="this.style.background='none';this.style.color='#A73E1E'">Retake placement test</button>
            </form>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" style="border: 1px solid #E0D8CC; background: none; color: #8A8378; border-radius: 999px; padding: 11px 20px; font: inherit; font-size: 12.5px; cursor: pointer; transition: all .2s;" onmouseover="this.style.borderColor='#A73E1E';this.style.color='#A73E1E'" onmouseout="this.style.borderColor='#E0D8CC';this.style.color='#8A8378'">Log out</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function fpFlipToggle(btn) {
    const on = btn.dataset.on === '1';
    const track = btn.querySelector('.fp-track');
    const knob = btn.querySelector('.fp-knob');
    if (on) {
        btn.dataset.on = '0';
        track.style.background = '#DED6C9';
        knob.style.transform = 'translateX(0)';
    } else {
        btn.dataset.on = '1';
        track.style.background = '#0E6B5C';
        knob.style.transform = 'translateX(19px)';
    }
}
</script>
@endsection
