@extends('layouts.app')

@section('title', 'Create account')
@section('crumb', 'Get started')

@section('content')
<div style="min-height: 100vh; display: grid; grid-template-columns: 1.05fr 1fr; background: #F6F2EC;">
    <div style="background: #17211E; color: #EFEAE2; padding: 46px; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
        <div style="position: absolute; width: 420px; height: 420px; border-radius: 50%; right: -140px; top: 40px; background: radial-gradient(circle, rgba(41,195,159,.32), transparent 68%); animation: drift 14s ease-in-out infinite;"></div>
        <div style="position: absolute; width: 320px; height: 320px; border-radius: 50%; left: -90px; bottom: -60px; background: radial-gradient(circle, rgba(224,96,59,.22), transparent 68%); animation: drift 18s ease-in-out infinite reverse;"></div>
        <div style="display: flex; align-items: center; gap: 12px; position: relative;">
            <div style="width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(140deg, #29C39F, #0E6B5C); display: grid; place-items: center; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; color: #06231D; font-size: 19px; animation: float 5s ease-in-out infinite;">E</div>
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 17px;">English Mentor AI</div>
        </div>
        <div style="position: relative; max-width: 460px;">
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 46px; font-weight: 800; letter-spacing: -1.6px; line-height: 1.08;">The coach that finds out what you actually know.</div>
            <div style="font-size: 15px; line-height: 1.65; color: #A6B4AE; margin-top: 20px;">One holistic placement test. A four-week plan built from your result. Every piece of writing read and explained, not just scored.</div>
            <div style="display: flex; gap: 26px; margin-top: 34px;">
                <div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 26px; font-weight: 700; color: #29C39F;">A1–C1</div>
                    <div style="font-size: 11.5px; color: #7E9089; margin-top: 2px;">levels covered</div>
                </div>
                <div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 26px; font-weight: 700; color: #29C39F;">4 weeks</div>
                    <div style="font-size: 11.5px; color: #7E9089; margin-top: 2px;">per roadmap cycle</div>
                </div>
                <div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 26px; font-weight: 700; color: #29C39F;">0 blocking</div>
                    <div style="font-size: 11.5px; color: #7E9089; margin-top: 2px;">AI calls, all queued</div>
                </div>
            </div>
        </div>
        <div style="font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: #7E9089; position: relative;">Simplon Maghreb × JobinTech · SRS v3.0</div>
    </div>

    <div style="display: grid; place-items: center; padding: 46px;">
        <div style="width: 100%; max-width: 384px; animation: rise .5s both cubic-bezier(.2,.9,.2,1);">
            <div style="display: flex; gap: 4px; background: #EDE7DE; border-radius: 999px; padding: 4px; margin-bottom: 28px;">
                <a href="/login" style="flex: 1; text-align: center; border: 0; border-radius: 999px; padding: 10px; font: inherit; font-size: 13px; font-weight: 500; transition: all .24s; background: transparent; color: #8A8378;">Sign in</a>
                <a href="/register" style="flex: 1; text-align: center; border: 0; border-radius: 999px; padding: 10px; font: inherit; font-size: 13px; font-weight: 500; transition: all .24s; background: #FFFDFA; color: #17211E;">Create account</a>
            </div>
            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 27px; font-weight: 700; letter-spacing: -.7px;">Create your learning space</div>
            <div style="font-size: 13.5px; color: #8A8378; margin-top: 6px;">No level to pick — the placement test decides it.</div>

            <form method="POST" action="/register" style="display: flex; flex-direction: column; gap: 14px; margin-top: 26px;">
                @csrf
                <label style="display: block;">
                    <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Full name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Hassan" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 12px; padding: 13px 15px; font-size: 14.5px; background: #FFFDFA; color: #17211E; outline: none; transition: border-color .2s, box-shadow .2s;">
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@example.com" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 12px; padding: 13px 15px; font-size: 14.5px; background: #FFFDFA; color: #17211E; outline: none; transition: border-color .2s, box-shadow .2s;">
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Password</span>
                    <input type="password" name="password" required placeholder="At least 8 characters" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 12px; padding: 13px 15px; font-size: 14.5px; background: #FFFDFA; color: #17211E; outline: none; transition: border-color .2s, box-shadow .2s;">
                </label>
                <label style="display: block;">
                    <span style="display: block; font-size: 11px; letter-spacing: 1.3px; text-transform: uppercase; color: #8A8378; margin-bottom: 7px;">Confirm password</span>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password" style="width: 100%; border: 1px solid #E0D8CC; border-radius: 12px; padding: 13px 15px; font-size: 14.5px; background: #FFFDFA; color: #17211E; outline: none; transition: border-color .2s, box-shadow .2s;">
                </label>

                <button type="submit" style="width: 100%; margin-top: 10px; border: 0; border-radius: 12px; padding: 15px; background: #17211E; color: #EFEAE2; font: inherit; font-size: 14px; font-weight: 600; cursor: pointer; transition: transform .2s, background .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.background='#0E6B5C'" onmouseout="this.style.transform='none';this.style.background='#17211E'">Create account →</button>
            </form>

            <div style="font-size: 12px; color: #A09889; margin-top: 22px; line-height: 1.55;">Sanctum token auth. Passwords hashed; every AI call runs on a queue.</div>
        </div>
    </div>
</div>
@endsection
