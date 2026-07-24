<?php

namespace App\Policies;

use App\Models\PlacementAnswer;
use App\Models\User;

class PlacementAnswerPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, PlacementAnswer $placementAnswer): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PlacementAnswer $placementAnswer): bool
    {
        return false;
    }

    public function delete(User $user, PlacementAnswer $placementAnswer): bool
    {
        return false;
    }
}
