<?php

use App\Models\Notification;
use App\Models\Roadmap;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $reminderDays = config('fluentpath.inactivity_reminder_days');
    $reminderSent = 0;
    $refreshTriggered = 0;

    $latestActivities = DB::table(DB::raw('(' . implode(' UNION ALL ', [
            'SELECT user_id, MAX(completed_at) as last_activity FROM lesson_progress WHERE completed_at IS NOT NULL GROUP BY user_id',
            'SELECT user_id, MAX(completed_at) as last_activity FROM quiz_attempts WHERE completed_at IS NOT NULL GROUP BY user_id',
            'SELECT user_id, MAX(submitted_at) as last_activity FROM writing_submissions WHERE submitted_at IS NOT NULL GROUP BY user_id',
        ]) . ') as activities'))
        ->select('user_id', DB::raw('MAX(last_activity) as last_activity'))
        ->groupBy('user_id');

    $inactiveUserIds = DB::table('users')
        ->joinSub($latestActivities, 'la', 'users.id', '=', 'la.user_id')
        ->where('users.role', 'student')
        ->where('la.last_activity', '<', now()->subDays($reminderDays))
        ->pluck('users.id');

    foreach ($inactiveUserIds as $userId) {
        $existing = Notification::where('user_id', $userId)
            ->where('title', 'We miss you!')
            ->where('created_at', '>=', now()->subDays($reminderDays))
            ->where('is_read', false)
            ->exists();

        if ($existing) {
            continue;
        }

        Notification::create([
            'user_id' => $userId,
            'title' => 'We miss you!',
            'message' => 'You haven\'t practiced in a while. Come back and continue your English learning journey!',
        ]);

        $reminderSent++;
    }

    $staleRoadmapUserIds = Roadmap::where('updated_at', '<', now()->subDays($reminderDays))
        ->pluck('user_id')
        ->unique()
        ->toArray();

    $activeUserIds = DB::table('users')
        ->joinSub($latestActivities, 'la', 'users.id', '=', 'la.user_id')
        ->where('users.role', 'student')
        ->where('la.last_activity', '>=', now()->subDays($reminderDays))
        ->pluck('users.id')
        ->toArray();

    $staleActiveUserIds = array_intersect($staleRoadmapUserIds, $activeUserIds);

    foreach ($staleActiveUserIds as $userId) {
        app(RecommendationService::class)->refreshForUser($userId);
        $refreshTriggered++;
    }

    Log::info("Daily maintenance: {$reminderSent} reminders sent, {$refreshTriggered} refreshes triggered.");
})->daily()->name('app:daily-maintenance');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
