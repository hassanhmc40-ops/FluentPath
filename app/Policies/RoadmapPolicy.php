<?php

namespace App\Policies;

use App\Models\Roadmap;
use App\Models\User;

class RoadmapPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Roadmap $roadmap): bool
    {
        return $roadmap->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Roadmap $roadmap): bool
    {
        return $roadmap->user_id === $user->id;
    }

    public function delete(User $user, Roadmap $roadmap): bool
    {
        return false;
    }
}
