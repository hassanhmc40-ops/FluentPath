<?php

namespace App\Policies;

use App\Models\Roadmap;
use App\Models\User;

class RoadmapPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Roadmap $roadmap): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Roadmap $roadmap): bool
    {
        return false;
    }

    public function delete(User $user, Roadmap $roadmap): bool
    {
        return false;
    }
}
