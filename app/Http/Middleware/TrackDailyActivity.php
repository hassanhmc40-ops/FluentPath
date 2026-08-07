<?php

namespace App\Http\Middleware;

use App\Models\UserDailyActivity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records today's row in user_daily_activity for authenticated students, so
 * simply opening the app marks the day as an active streak day. One row per
 * user per day (unique constraint), so repeated requests are free.
 */
class TrackDailyActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isAdmin()) {
            UserDailyActivity::firstOrCreate([
                'user_id' => $user->id,
                'activity_date' => now()->toDateString(),
            ]);
        }

        return $next($request);
    }
}
