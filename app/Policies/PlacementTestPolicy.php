<?php

namespace App\Policies;

use App\Models\PlacementTest;
use App\Models\User;

class PlacementTestPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, PlacementTest $placementTest): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PlacementTest $placementTest): bool
    {
        return false;
    }

    public function delete(User $user, PlacementTest $placementTest): bool
    {
        return false;
    }
}
