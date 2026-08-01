<?php

namespace App\Policies;

use App\Models\RoadmapWeekLesson;
use App\Models\User;

class RoadmapWeekLessonPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RoadmapWeekLesson $roadmapWeekLesson): bool
    {
        return $roadmapWeekLesson->roadmapWeek->roadmap->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, RoadmapWeekLesson $roadmapWeekLesson): bool
    {
        return $roadmapWeekLesson->roadmapWeek->roadmap->user_id === $user->id;
    }

    public function delete(User $user, RoadmapWeekLesson $roadmapWeekLesson): bool
    {
        return false;
    }
}
