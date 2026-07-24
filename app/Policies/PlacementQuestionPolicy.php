<?php

namespace App\Policies;

use App\Models\PlacementQuestion;
use App\Models\User;

class PlacementQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, PlacementQuestion $placementQuestion): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PlacementQuestion $placementQuestion): bool
    {
        return false;
    }

    public function delete(User $user, PlacementQuestion $placementQuestion): bool
    {
        return false;
    }
}
