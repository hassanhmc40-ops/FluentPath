<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Quiz $quiz): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return false;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return false;
    }
}
