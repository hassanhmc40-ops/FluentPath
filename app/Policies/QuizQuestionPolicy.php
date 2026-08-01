<?php

namespace App\Policies;

use App\Models\QuizQuestion;
use App\Models\User;

class QuizQuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, QuizQuestion $quizQuestion): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, QuizQuestion $quizQuestion): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, QuizQuestion $quizQuestion): bool
    {
        return $user->isAdmin();
    }
}
