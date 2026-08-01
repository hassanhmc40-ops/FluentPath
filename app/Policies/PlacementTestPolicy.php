<?php

namespace App\Policies;

use App\Models\PlacementTest;
use App\Models\User;

class PlacementTestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlacementTest $placementTest): bool
    {
        return $placementTest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlacementTest $placementTest): bool
    {
        return $placementTest->user_id === $user->id;
    }

    public function delete(User $user, PlacementTest $placementTest): bool
    {
        return false;
    }
}
