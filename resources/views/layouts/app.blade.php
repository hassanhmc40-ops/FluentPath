<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — English Mentor AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/design.css') }}">
    @yield('head')
</head>
<body>
    @php
        $user = auth()->user();
        $unreadCount = 0;
        $streak = 0;
        $queuedCount = 0;
        $placementLevel = null;
        $userInitial = 'E';

        if ($user) {
            $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                ->where('is_read', false)->count();

            $queuedCount = \App\Models\PlacementTest::whereIn('status', ['pending','processing'])->count()
                + \App\Models\WritingSubmission::whereIn('status', ['pending','processing'])->count();

            $userInitial = strtoupper(substr($user->name, 0, 1));

            if (! $user->isAdmin()) {
                $placementLevel = \App\Models\PlacementTest::where('user_id', $user->id)
                    ->where('status', 'analyzed')->latest('id')->first()?->cefr_level?->value;

                $dates = collect();
                $dates = $dates->merge(\App\Models\LessonProgress::where('user_id', $user->id)
                    ->whereNotNull('completed_at')->selectRaw('DATE(completed_at) as d')->pluck('d'));
                $dates = $dates->merge(\App\Models\QuizAttempt::where('user_id', $user->id)
                    ->whereNotNull('completed_at')->selectRaw('DATE(completed_at) as d')->pluck('d'));
                $dates = $dates->merge(\App\Models\WritingSubmission::where('user_id', $user->id)
                    ->whereNotNull('submitted_at')->selectRaw('DATE(submitted_at) as d')->pluck('d'));
                $dates = $dates->merge(\App\Models\UserDailyActivity::where('user_id', $user->id)
                    ->selectRaw('activity_date as d')->pluck('d'));
                $uniqueDates = $dates->unique()->sortDesc()->values();
                $expected = now()->startOfDay();
                foreach ($uniqueDates as $dateStr) {
                    $date = \Carbon\Carbon::parse($dateStr)->startOfDay();
                    if ($date->ne($expected)) { break; }
                    $streak++;
                    $expected = $expected->subDay();
                }
                // A user can never have been active on more days than their account has existed.
                $cap = \Carbon\Carbon::parse($user->created_at)->startOfDay()->diffInDays(now()->startOfDay()) + 1;
                $streak = min($streak, $cap);
            }
        }

        $recentNotifs = $user
            ? \App\Models\Notification::where('user_id', $user->id)->orderByDesc('created_at')->limit(4)->get()
            : collect();
    @endphp

    @auth
    <div style="min-height: 100vh; display: grid; grid-template-columns: 252px 1fr; background: #F6F2EC;">

        <aside style="background: #17211E; color: #EFEAE2; padding: 26px 20px; display: flex; flex-direction: column; gap: 26px; position: sticky; top: 0; height: 100vh; overflow-y: auto;">
            <div style="display: flex; align-items: center; gap: 11px;">
                <div style="width: 34px; height: 34px; border-radius: 11px; background: linear-gradient(140deg, #29C39F, #0E6B5C); display: grid; place-items: center; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; color: #06231D; font-size: 17px; animation: float 5s ease-in-out infinite;">E</div>
                <div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 15px; letter-spacing: -.2px;">English Mentor</div>
                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #7E9089;">{{ $user->isAdmin() ? 'Admin console' : 'AI Coach' }}</div>
                </div>
            </div>

            <nav style="display: flex; flex-direction: column; gap: 3px;">
                @if ($user->isAdmin())
                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #61726C; padding: 16px 12px 7px;">Platform</div>
                    <a href="/admin" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('admin') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('admin') && !request()->is('admin/*') ? '#29C39F' : 'transparent' }}; transition: background .22s, color .22s, transform .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('admin') && !request()->is('admin/*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('admin') && !request()->is('admin/*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Overview</span>
                    </a>
                    <a href="/admin/students" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('admin/students') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('admin/students') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('admin/students') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('admin/students') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Students</span>
                    </a>

                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #61726C; padding: 16px 12px 7px;">Catalog</div>
                    <a href="/admin/lessons" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('admin/lessons*') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('admin/lessons*') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('admin/lessons*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('admin/lessons*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Lessons</span>
                    </a>
                    <a href="/admin/quizzes" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('admin/quizzes*') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('admin/quizzes*') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('admin/quizzes*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('admin/quizzes*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Exercises</span>
                    </a>
                    <a href="/admin/placement-questions" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('admin/placement-questions*') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('admin/placement-questions*') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('admin/placement-questions*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('admin/placement-questions*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Placement test</span>
                    </a>

                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #61726C; padding: 16px 12px 7px;">Account</div>
                    <a href="/notifications" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('notifications') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('notifications') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('notifications') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('notifications') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Notifications</span>
                        @if ($unreadCount > 0)
                            <span style="font-size: 10.5px; font-weight: 600; padding: 2px 7px; border-radius: 999px; background: #E0603B; color: #FFF6F2;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="/settings" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('settings') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('settings') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('settings') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('settings') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Settings</span>
                    </a>
                @else
                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #61726C; padding: 16px 12px 7px;">Learn</div>
                    <a href="/dashboard" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('dashboard') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('dashboard') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('dashboard') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('dashboard') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Dashboard</span>
                    </a>
                    <a href="/placement-test" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('placement-test*') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('placement-test*') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('placement-test*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('placement-test*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Placement test</span>
                    </a>
                    <a href="/roadmap" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('roadmap') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('roadmap') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('roadmap') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('roadmap') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Roadmap</span>
                    </a>
                    <a href="/lessons" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('lessons*') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('lessons*') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('lessons*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('lessons*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Lessons</span>
                    </a>
                    <a href="/quizzes" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('quizzes*') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('quizzes*') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('quizzes*') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('quizzes*') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Exercises</span>
                    </a>

                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #61726C; padding: 16px 12px 7px;">Practice</div>
                    <a href="/writing" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('writing') && !request()->is('writing/submissions') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('writing') && !request()->is('writing/submissions') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('writing') && !request()->is('writing/submissions') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('writing') && !request()->is('writing/submissions') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Writing studio</span>
                    </a>
                    <a href="/writing/submissions" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('writing/submissions') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('writing/submissions') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('writing/submissions') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('writing/submissions') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">My submissions</span>
                    </a>

                    <div style="font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: #61726C; padding: 16px 12px 7px;">Account</div>
                    <a href="/progress" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('progress') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('progress') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('progress') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('progress') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Progress</span>
                    </a>
                    <a href="/notifications" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('notifications') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('notifications') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('notifications') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('notifications') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Notifications</span>
                        @if ($unreadCount > 0)
                            <span style="font-size: 10.5px; font-weight: 600; padding: 2px 7px; border-radius: 999px; background: #E0603B; color: #FFF6F2;">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="/settings" style="display: flex; align-items: center; gap: 11px; padding: 10px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500; color: {{ request()->is('settings') ? '#06231D' : '#B9C5C0' }}; background: {{ request()->is('settings') ? '#29C39F' : 'transparent' }}; transition: all .22s;" onmouseover="this.style.background='#24312D';this.style.transform='translateX(3px)';this.style.color='#EFEAE2'" onmouseout="this.style.background='{{ request()->is('settings') ? '#29C39F' : 'transparent' }}';this.style.transform='none';this.style.color='{{ request()->is('settings') ? '#06231D' : '#B9C5C0' }}'">
                        <span style="flex: 1;">Settings</span>
                    </a>
                @endif
            </nav>

            <div style="margin-top: auto; display: flex; flex-direction: column; gap: 12px;">
                @if (! $user->isAdmin())
                    <div style="background: #202D29; border: 1px solid #2C3A35; border-radius: 14px; padding: 15px;">
                        <div style="font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: #7E9089; margin-bottom: 9px;">Streak</div>
                        <div style="display: flex; align-items: baseline; gap: 7px;">
                            <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 28px; font-weight: 700; color: #29C39F; line-height: 1;">{{ $streak }}</div>
                            <div style="font-size: 12px; color: #A6B4AE;">days active</div>
                        </div>
                        <div style="display: flex; gap: 4px; margin-top: 11px;">
                            @foreach ([1,1,0,1,1,1,0] as $v)
                                <div style="flex: 1; height: 24px; border-radius: 5px; background: {{ $v ? '#29C39F' : '#2C3A35' }}; transform-origin: bottom; animation: grow .5s {{ $loop->index * 0.06 }}s both cubic-bezier(.2,.9,.2,1);"></div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div style="display: flex; align-items: center; gap: 10px; padding: 2px;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #E0603B; display: grid; place-items: center; font-size: 12px; font-weight: 600; color: #FFF6F2;">{{ $userInitial }}</div>
                    <div style="line-height: 1.3; flex: 1;">
                        <div style="font-size: 13px; font-weight: 500;">{{ $user->name }}</div>
                        <div style="font-size: 11px; color: #7E9089;">{{ $user->isAdmin() ? 'Admin · Catalog' : 'Student · ' . ($placementLevel ?? 'No level yet') }}</div>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" style="border: 0; background: none; color: #61726C; cursor: pointer; font-size: 14px;" title="Logout">↪</button>
                    </form>
                </div>
            </div>
        </aside>

        <main style="min-width: 0;">
            <header style="display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 20px 38px; border-bottom: 1px solid #E5DDD2; background: rgba(246,242,236,.88); backdrop-filter: blur(9px); position: sticky; top: 0; z-index: 20;">
                <div>
                    <div style="font-size: 10.5px; letter-spacing: 1.7px; text-transform: uppercase; color: #8A8378;">@yield('crumb', 'English Mentor AI')</div>
                    <div style="font-family: 'Bricolage Grotesque', sans-serif; font-size: 22px; font-weight: 700; letter-spacing: -.4px; margin-top: 3px;">@yield('title', 'Dashboard')</div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    @if ($queuedCount > 0)
                        <div style="display: flex; align-items: center; gap: 8px; background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 999px; padding: 8px 15px; font-size: 12.5px; color: #6C6A63;">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #29C39F; animation: pulse 2.2s infinite;"></span>
                            <span>Queue healthy · {{ $queuedCount }} {{ Str::plural('job', $queuedCount) }}</span>
                        </div>
                    @endif
                    <button onclick="document.getElementById('bell-panel').classList.toggle('fp-bell-open')" style="position: relative; width: 40px; height: 40px; border-radius: 12px; border: 1px solid #E5DDD2; background: #FFFDFA; cursor: pointer; font-size: 15px; transition: transform .2s, box-shadow .2s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(23,33,30,.1)'" onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                        <span style="opacity: .8;">🔔</span>
                        @if ($unreadCount > 0)
                            <span style="position: absolute; top: 7px; right: 7px; width: 8px; height: 8px; border-radius: 50%; background: #E0603B; animation: blink 1.8s infinite;"></span>
                        @endif
                    </button>
                </div>
            </header>

            <div id="bell-panel" style="display: none; position: fixed; right: 34px; top: 84px; width: 328px; background: #FFFDFA; border: 1px solid #E5DDD2; border-radius: 16px; box-shadow: 0 26px 60px rgba(23,33,30,.16); padding: 8px; z-index: 40; animation: rise .3s both;">
                <div style="padding: 10px 12px; font-size: 10.5px; letter-spacing: 1.5px; text-transform: uppercase; color: #8A8378;">Notifications</div>
                @forelse ($recentNotifs as $notif)
                    <div style="display: flex; gap: 11px; padding: 11px 12px; border-radius: 11px; transition: background .2s;" onmouseover="this.style.background='#F3EFE8'" onmouseout="this.style.background='transparent'">
                        <span style="width: 6px; height: 6px; border-radius: 50%; margin-top: 6px; flex: none; background: {{ $notif->is_read ? '#CFC6B8' : '#29C39F' }};"></span>
                        <div>
                            <div style="font-size: 13px; font-weight: 500;">{{ $notif->title }}</div>
                            <div style="font-size: 11.5px; color: #8A8378; margin-top: 2px;">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div style="padding: 14px 12px; font-size: 12.5px; color: #8A8378;">No notifications yet.</div>
                @endforelse
                <a href="/notifications" style="display: block; width: 100%; margin-top: 4px; border: 0; background: #F3EFE8; border-radius: 11px; padding: 11px; font: inherit; font-size: 12.5px; text-align: center; color: #0E6B5C;">See all notifications</a>
            </div>

            @if (session('success'))
                <div style="padding: 18px 38px 0;">
                    <div style="background: #E3F2EE; border: 1px solid #CDE7E0; color: #0A5347; padding: 13px 18px; border-radius: 14px; font-size: 13.5px; font-weight: 500; animation: rise .3s both;">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div style="padding: 18px 38px 0;">
                    <div style="background: #FBE7DF; border: 1px solid #F3CDBC; color: #A73E1E; padding: 13px 18px; border-radius: 14px; font-size: 13.5px; font-weight: 500; animation: rise .3s both;">{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div style="padding: 18px 38px 0;">
                    <div style="background: #FBE7DF; border: 1px solid #F3CDBC; color: #A73E1E; padding: 13px 18px; border-radius: 14px; font-size: 13.5px; font-weight: 500; animation: rise .3s both;">
                        @foreach ($errors->all() as $error) {{ $error }} @endforeach
                    </div>
                </div>
            @endif

            <div style="padding: 30px 38px 60px;">
                @yield('content')
            </div>
        </main>
    </div>
    @else
    <main style="min-height: 100vh;">
        @yield('content')
    </main>
    @endauth

    <style>
        .fp-bell-open { display: block !important; }
    </style>
    @yield('scripts')
</body>
</html>
