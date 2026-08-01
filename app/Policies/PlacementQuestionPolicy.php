<?php

namespace App\Policies;

use App\Models\PlacementQuestion;
use App\Models\User;

class PlacementQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PlacementQuestion $placementQuestion): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, PlacementQuestion $placementQuestion): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, PlacementQuestion $placementQuestion): bool
    {
        return $user->isAdmin();
    }
}
