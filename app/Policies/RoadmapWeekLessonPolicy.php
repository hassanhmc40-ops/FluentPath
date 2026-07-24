<?php

namespace App\Policies;

use App\Models\RoadmapWeekLesson;
use App\Models\User;

class RoadmapWeekLessonPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, RoadmapWeekLesson $roadmapWeekLesson): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, RoadmapWeekLesson $roadmapWeekLesson): bool
    {
        return false;
    }

    public function delete(User $user, RoadmapWeekLesson $roadmapWeekLesson): bool
    {
        return false;
    }
}
