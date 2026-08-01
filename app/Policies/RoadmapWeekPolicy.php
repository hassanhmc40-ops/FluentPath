<?php

namespace App\Policies;

use App\Models\RoadmapWeek;
use App\Models\User;

class RoadmapWeekPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RoadmapWeek $roadmapWeek): bool
    {
        return $roadmapWeek->roadmap->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, RoadmapWeek $roadmapWeek): bool
    {
        return $roadmapWeek->roadmap->user_id === $user->id;
    }

    public function delete(User $user, RoadmapWeek $roadmapWeek): bool
    {
        return false;
    }
}
