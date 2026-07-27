<?php

namespace App\Policies;

use App\Models\RoadmapWeek;
use App\Models\User;

class RoadmapWeekPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, RoadmapWeek $roadmapWeek): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, RoadmapWeek $roadmapWeek): bool
    {
        return false;
    }

    public function delete(User $user, RoadmapWeek $roadmapWeek): bool
    {
        return false;
    }
}
