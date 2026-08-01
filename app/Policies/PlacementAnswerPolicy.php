<?php

namespace App\Policies;

use App\Models\PlacementAnswer;
use App\Models\User;

class PlacementAnswerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlacementAnswer $placementAnswer): bool
    {
        return $placementAnswer->placementTest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PlacementAnswer $placementAnswer): bool
    {
        return $placementAnswer->placementTest->user_id === $user->id;
    }

    public function delete(User $user, PlacementAnswer $placementAnswer): bool
    {
        return false;
    }
}
